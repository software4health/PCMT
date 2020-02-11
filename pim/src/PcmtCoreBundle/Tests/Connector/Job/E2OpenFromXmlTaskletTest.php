<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Connector\Job\E2OpenFromXmlTasklet;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
use PcmtCoreBundle\Service\Query\ESQuery;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class E2OpenFromXmlTaskletTest extends KernelTestCase
{
    /** @var string */
    private $testFilePath = 'src/PcmtCoreBundle/Tests/TestResources/TestImportE2OpenFile.xml';

    /** @var ProductRepositoryInterface|Mock */
    private $productRepositoryMock;

    /** @var SaverInterface|Mock */
    private $productSaverMock;

    /** @var ProductBuilderInterface|Mock */
    private $productBuilderMock;

    /** @var ESQuery|Mock */
    private $esQueryMock;

    /** @var TradeItemXmlProcessor|Mock */
    private $tradeItemProcessorMock;

    /** @var StepExecution */
    private $stepExecutionMock;

    /** @var JobParameters|Mock */
    private $jobParametersMock;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->productSaverMock = $this->createMock(ProductSaver::class);
        $this->productBuilderMock = $this->createMock(ProductBuilder::class);
        $this->esQueryMock = $this->createMock(ESQuery::class);
        $this->tradeItemProcessorMock = $this->createMock(TradeItemXmlProcessor::class);

        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->once())
            ->method('get')
            ->with('xmlFilePath')
            ->willReturn($this->testFilePath);

        parent::setUp();
    }

    /**
     * @dataProvider dataProcessForANewProduct
     */
    public function testProcessForANewProduct(array $elasticSearchQueryResultWithoutProductIndexed): void
    {
        $importTasklet = $this->getE2OpenFromXmlTaskletInstance();

        $this->esQueryMock->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturn($elasticSearchQueryResultWithoutProductIndexed);

        $this->productBuilderMock->expects($this->atLeastOnce())
            ->method('createProduct')
            ->willReturn($product = new Product());

        $this->tradeItemProcessorMock->expects($this->exactly(3))
            ->method('setProductToUpdate');

        $this->productSaverMock->expects($this->atLeastOnce())
            ->method('save');

        $importTasklet->execute();
    }

    /**
     * @dataProvider dataProcessForExistingProduct
     */
    public function testProcessForAnExistingProduct(array $elasticSearchQueryResultWithProductIndexed): void
    {
        $importTasklet = $this->getE2OpenFromXmlTaskletInstance();

        $this->esQueryMock->expects($this->atLeastOnce())
            ->method('execute')
            ->willReturn($elasticSearchQueryResultWithProductIndexed);

        $this->productRepositoryMock->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn($product = new Product());

        $this->tradeItemProcessorMock->expects($this->exactly(3))
            ->method('setProductToUpdate');

        $this->productSaverMock->expects($this->atLeastOnce())
            ->method('save');

        $importTasklet->execute();
    }

    public function dataProcessForANewProduct(): array
    {
        return [
            [
                [
                    'hits' => [
                        'total' => 0,
                        'hits'  => [],
                    ],
                ],
            ],
        ];
    }

    public function dataProcessForExistingProduct(): array
    {
        return [
            [
                [
                    'hits' => [
                        'total' => 1,
                        'hits'  => [
                            0 => [
                                '_id' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getE2OpenFromXmlTaskletInstance(): TaskletInterface
    {
        $tasklet = new E2OpenFromXmlTasklet(
            $this->productRepositoryMock,
            $this->productSaverMock,
            $this->productBuilderMock,
            $this->tradeItemProcessorMock,
            $this->esQueryMock
        );
        $tasklet->setStepExecution($this->stepExecutionMock);

        return $tasklet;
    }
}
