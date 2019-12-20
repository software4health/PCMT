<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;
use PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class E2OpenFromXmlTasklet implements TaskletInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var Service */
    private $xmlReader;

    /** @var mixed */
    private $item;

    /** @var string[] */
    protected $valueMapping = [];

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ClientRegistry */
    private $elasticClientRegistry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        ClientRegistry $clientRegistry
    ) {
        $this->xmlReader = new Service();
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->familyRepository = $familyRepository;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->productBuilder = $productBuilder;
        $this->elasticClientRegistry = $clientRegistry;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $fileGetContentsWrapper = new FileGetContentsWrapper();
        $filePath = $this->stepExecution->getJobParameters()
            ->get('xmlFilePath');

        $xmlInput = $fileGetContentsWrapper->fileGetContents($filePath);
        $this->xmlReader->elementMap = [
            '{http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader}StandardBusinessDocumentHeader' => 'Sabre\Xml\Deserializer\enum',
            '{}tradeItem'                                                                                           => function (
                Reader $reader
            ): void {
                $subTree = $reader->parseInnerTree();
                array_walk(
                    $subTree,
                    function ($element): void {
                        if ('{}gtin' === $element['name']) {
                            $this->item = $this->instantiateProduct($element);
                        }
                        $this->addToValueMapping($element);
                    }
                );

                $this->processProduct();
            },
        ];
        try {
            $this->xmlReader->parse($xmlInput);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    private function addToValueMapping(array $element, string $parent = ''): void
    {
        $parentMappingRequired = [
            '{}gln',
            '{}partyName',
            '{}partyAddress',
            '{}depth',
            'measurementUnitCode',
            'languageCode',
        ];

        if (!empty($element['attributes'])) {
            foreach ($element['attributes'] as $name => $value) {
                $newElement['name'] = $name;
                $newElement['value'] = $value;
            }
            $this->addToValueMapping($newElement, $element['name']);
        }

        if (!is_array($element['value'])) {
            switch ($value = $element['name']) {
                case in_array($value, $parentMappingRequired):
                    $this->valueMapping[$parent . $element['name']] = E2OpenMapping::mapValue($element['value']);
                    break;
                default:
                    $this->valueMapping[$element['name']] = E2OpenMapping::mapValue($element['value']);
            }

            return;
        }

        foreach ($element['value'] as $subElement) {
            $this->addToValueMapping($subElement, $element['name']);
        }
    }

    private function getMapping(string $key): ?string
    {
        return E2OpenMapping::findMappingForKey($key);
    }

    private function processProduct(): void
    {
        foreach ($this->valueMapping as $e2OpenKey => $value) {
            if (!$mapping = $this->getMapping($e2OpenKey)) {
                continue;
            }
            $pcmtProductFamily = $this->familyRepository->findOneBy(['code' => 'GS1_GDSN']);
            $pcmtAttribute = $this->attributeRepository->findOneBy(
                [
                    'code' => $mapping,
                ]
            );
            if ($pcmtAttribute && $pcmtProductFamily) {
                if ($pcmtProductFamily->hasAttribute($pcmtAttribute)) {
                    $valuesToUpdate[$pcmtAttribute->getCode()]['data']['data'] = $value;
                    $valuesToUpdate[$pcmtAttribute->getCode()]['data']['locale'] = null;
                    $valuesToUpdate[$pcmtAttribute->getCode()]['data']['scope'] = null;
                    $this->productUpdater->update(
                        $this->item,
                        [
                            'values' => $valuesToUpdate,
                        ]
                    );
                }
            }
        }

        $this->productSaver->save($this->item);
    }

    private function instantiateProduct(array $element): ProductInterface
    {
        $gtinIdentifier = $element['value'];

        $elasticIndexName = 'akeneo_pim_product';
        $esClient = $this->getElasticSearchClient($elasticIndexName);
        $esQuery = [];
        $esQuery['query']['bool']['must'] = [
            [
                'match' => [
                    'values.GTIN-text.<all_channels>.<all_locales>' => $gtinIdentifier,
                ],
            ],
            [
                'match' => [
                    'family.code' => 'GS1_GDSN',
                ],
            ],
        ];
        $result = $esClient->search('pim_catalog_product', $esQuery);
        if ($result['hits']['total'] < 1) {
            return $this->createNewProductInstance($gtinIdentifier);
        }
        $response = $result['hits']['hits'];

        return $this->getProductInstanceFromRepository((int) $response[0]['_id']);
    }

    private function createNewProductInstance(string $identifier): ProductInterface
    {
        return $this->productBuilder->createProduct(
            $identifier,
            'GS1_GDSN'
        );
    }

    private function getProductInstanceFromRepository(int $id): ProductInterface
    {
        return $this->productRepository->findOneBy(
            [
                'id' => $id,
            ]
        );
    }

    private function getElasticSearchClient(string $indexName): Client
    {
        foreach ($this->elasticClientRegistry->getClients() as $client) {
            if ($client->getIndexName() === $indexName) {
                return $client;
            }
        }
        throw new \InvalidArgumentException('Wrong index ' . $indexName . ' passed');
    }
}
