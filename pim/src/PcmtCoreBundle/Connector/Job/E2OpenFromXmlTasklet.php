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
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
use PcmtCoreBundle\Service\Query\ESQuery;
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

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ESQuery */
    private $esQueryService;

    /** @var TradeItemXmlProcessor */
    private $nodeProcessor;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        TradeItemXmlProcessor $nodeProcessor,
        ESQuery $esQueryService
    ) {
        $this->xmlReader = new Service();
        $this->productRepository = $productRepository;
        $this->productSaver = $productSaver;
        $this->productBuilder = $productBuilder;
        $this->esQueryService = $esQueryService;
        $this->nodeProcessor = $nodeProcessor;
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
                        $this->nodeProcessor->setProductToUpdate($this->item);
                        break;
                    }
                }
                if (!$this->item) {
                    throw new \Exception('No item has been created.');
                }
                array_walk(
                    $subTree,
                    function ($element): void {
                        $this->nodeProcessor->processNode($element);
                    }
                );

                $this->productSaver->save($this->item);
            },
        ];

        $this->xmlReader->parse($xmlInput);
    }

    private function instantiateProduct(array $element): ProductInterface
    {
        $identifier = $element['value'];
        $esQuery['query']['bool']['must'] = [
            [
                'match' => [
                    'identifier' => $identifier,
                ],
            ],
        ];
        $result = $this->esQueryService->execute($esQuery);

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
}
