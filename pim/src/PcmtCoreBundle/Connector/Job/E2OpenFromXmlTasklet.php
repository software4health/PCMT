<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
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

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var TradeItemXmlProcessor */
    private $nodeProcessor;

    /** @var ProductQueryBuilderFactory */
    private $pqbFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SaverInterface $productSaver,
        ProductBuilderInterface $productBuilder,
        TradeItemXmlProcessor $nodeProcessor,
        ProductQueryBuilderFactory $pqbFactory,
        LoggerInterface $logger
    ) {
        $this->xmlReader = new Service();
        $this->productSaver = $productSaver;
        $this->productBuilder = $productBuilder;
        $this->nodeProcessor = $nodeProcessor;
        $this->pqbFactory = $pqbFactory;
        $this->logger = $logger;
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
        $gtinValue = $element['value'];
        $this->logger->info('Instantiating: '. $gtinValue);

        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
        ]);
        $pqb->addFilter('GTIN', '=', $gtinValue);
        $pqb->addFilter('family', Operators::IN_LIST, [E2OpenAttributesService::FAMILY_CODE]);

        $productsCursor = $pqb->execute();
        $product = $productsCursor->current();

        if ($product) {
            /** @var ProductInterface $product */
            $this->logger->info('Product in GS1_GDSN family found, id: '. $product->getId());

            return $product;
        }

        $this->logger->info('Product not found in GS1_GDSN family, creating new');

        $uniqueId = microtime() . '-'. $gtinValue;

        return $this->createNewProductInstance($uniqueId);
    }

    private function createNewProductInstance(string $identifier): ProductInterface
    {
        return $this->productBuilder->createProduct(
            $identifier,
            E2OpenAttributesService::FAMILY_CODE
        );
    }
}
