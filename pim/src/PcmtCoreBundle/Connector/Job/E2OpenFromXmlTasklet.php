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
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper;
use Psr\Log\LoggerInterface;
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

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ClientRegistry */
    private $elasticClientRegistry;

    /** @var LoggerInterface */
    private $logger;

    /** @var \PcmtCoreBundle\Service\E2Open\E2OpenAttributesService */
    private $attributesService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        ClientRegistry $clientRegistry,
        LoggerInterface $logger,
        E2OpenAttributesService $attributesService
    ) {
        $this->xmlReader = new Service();
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->productBuilder = $productBuilder;
        $this->elasticClientRegistry = $clientRegistry;
        $this->logger = $logger;
        $this->attributesService = $attributesService;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $filePath = $this->stepExecution->getJobParameters()
            ->get('xmlFilePath');
        $this->processFile($filePath);
    }

    private function processNode(array $element, string $parent = ''): void
    {
        if (!empty($element['attributes'])) {
            // there are some additional attributes in node, process them individually
            foreach ($element['attributes'] as $name => $value) {
                $newElement = [
                    'name'  => $name,
                    'value' => $value,
                ];
                $this->processNode($newElement, $element['name']);
            }
            // but don't finish processing here, process also whole node.
        }

        if (is_array($element['value'])) {
            // there are still further nodes
            foreach ($element['value'] as $subElement) {
                $this->processNode($subElement, $element['name']);
            }
            // finish processing node in such case
            return;
        }

        $name = $parent.$element['name'];
        if (!$mappedAttributeCode = E2OpenMapping::findMappingForKey($name)) {
            $name = $element['name'];
            $mappedAttributeCode = E2OpenMapping::findMappingForKey($name);
        }

        if (!$mappedAttributeCode) {
            // no mapping defined for this node
            return;
        }
        try {
            $value = E2OpenMapping::mapValue($element['value']);
            $this->processProductAttributeValue($mappedAttributeCode, $value, $element['attributes'] ?? []);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Processing key ' . $element['name'] . ' failed. Key and value will be ignored. ' .
                'Details: ' . $exception->getMessage()
            );
        }
    }

    private function processFile(string $filePath): void
    {
        $fileGetContentsWrapper = new FileGetContentsWrapper();
        $xmlInput = $fileGetContentsWrapper->fileGetContents($filePath);
        $xmlHeader = '{http://www.unece.org/cefact/namespaces/StandardBusinessDocumentHeader}StandardBusinessDocumentHeader';
        $xmlProductInformationRootNode = '{}tradeItem';
        $this->xmlReader->elementMap = [
            $xmlHeader                     => 'Sabre\Xml\Deserializer\enum',
            $xmlProductInformationRootNode => function (
                Reader $reader
            ): void {
                $subTree = $reader->parseInnerTree();
                foreach ($subTree as $element) {
                    if ('{}gtin' === $element['name']) {
                        $this->item = $this->instantiateProduct($element);
                        break;
                    }
                }
                if (!$this->item) {
                    throw new \Exception('No item has been created.');
                }
                array_walk(
                    $subTree,
                    function ($element): void {
                        $this->processNode($element);
                    }
                );

                $this->productSaver->save($this->item);
            },
        ];

        $this->xmlReader->parse($xmlInput);
    }

    /**
     * @param string|int $value
     *
     * @throws \Exception
     */
    private function processProductAttributeValue(string $mappedAttributeCode, $value, ?array $nodeAttributes): void
    {
        $pcmtAttribute = $this->attributesService->getForCode($mappedAttributeCode);
        if (!$pcmtAttribute) {
            throw new \Exception('Attribute not found for ' . $mappedAttributeCode);
        }

        $unit = null;
        if (E2OpenAttributesService::MEASURE_UNIT === $pcmtAttribute->getMetricFamily()) {
            foreach ($nodeAttributes as $name => $v) {
                if (false !== mb_stripos($name, 'measurementUnitCode')) {
                    if ($u = $this->attributesService->getMeasureUnitForSymbol($v)) {
                        $unit = $u;
                    }
                }
            }
        }

        if ($unit) {
            // if measurement unit found, this is a special metric field and we need to send array instead of string/int
            $value = [
                'unit'   => $unit,
                'amount' => $value,
            ];
        }

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

    private function instantiateProduct(array $element): ProductInterface
    {
        $identifier = $element['value'];
        $elasticIndexName = 'akeneo_pim_product';
        $esClient = $this->getElasticSearchClient($elasticIndexName);
        $esQuery = [];
        $esQuery['query']['bool']['must'] = [
            [
                'match' => [
                    'identifier' => $identifier,
                ],
            ],
        ];
        $result = $esClient->search('pim_catalog_product', $esQuery);
        if ($result['hits']['total'] < 1) {
            return $this->createNewProductInstance($identifier);
        }
        $response = $result['hits']['hits'];

        return $this->getProductInstanceFromRepository((int) $response[0]['_id']);
    }

    private function createNewProductInstance(string $identifier): ProductInterface
    {
        return $this->productBuilder->createProduct(
            $identifier,
            E2OpenAttributesService::FAMILY_CODE
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
