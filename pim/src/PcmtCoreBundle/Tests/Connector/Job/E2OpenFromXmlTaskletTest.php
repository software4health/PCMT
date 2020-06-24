<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Connector\Job\E2OpenFromXmlTasklet;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
use PcmtCoreBundle\Tests\TestDataBuilder\FamilyBuilder;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class E2OpenFromXmlTaskletTest extends KernelTestCase
{
    /** @var string */
    private $testFilePath = 'src/PcmtCoreBundle/Tests/TestResources/TestImportE2OpenFile.xml';

    /** @var SaverInterface|Mock */
    private $productSaverMock;

    /** @var ProductBuilderInterface|Mock */
    private $productBuilderMock;

    /** @var TradeItemXmlProcessor|Mock */
    private $tradeItemProcessorMock;

    /** @var StepExecution */
    private $stepExecutionMock;

    /** @var JobParameters|Mock */
    private $jobParametersMock;

    /** @var ProductQueryBuilderFactory|Mock */
    private $productQueryBuilderFactoryMock;

    /** @var CursorInterface|Mock */
    private $productsCursorMock;

    /** @var ProductQueryBuilderInterface|Mock */
    private $productQueryBuilderMock;

    /** @var LoggerInterface|Mock */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->productSaverMock = $this->createMock(ProductSaver::class);
        $this->productBuilderMock = $this->createMock(ProductBuilder::class);
        $this->tradeItemProcessorMock = $this->createMock(TradeItemXmlProcessor::class);
        $this->productQueryBuilderFactoryMock = $this->createMock(ProductQueryBuilderFactory::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->productsCursorMock = $this->createMock(CursorInterface::class);
        $this->productQueryBuilderFactoryMock->method('create')->willReturn(
            $this->productQueryBuilderMock
        );
        $this->productQueryBuilderMock->method('execute')->willReturn($this->productsCursorMock);

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

    public function dataProcess(): array
    {
        $family = (new FamilyBuilder())->withCode(E2OpenAttributesService::FAMILY_CODE)->build();
        $product = (new \PcmtCoreBundle\Tests\TestDataBuilder\ProductBuilder())->withFamily($family)->build();

        return [
            [$product],
        ];
    }

    /** @dataProvider dataProcess */
    public function testProcessForANewProduct(ProductInterface $product): void
    {
        $importTasklet = $this->getE2OpenFromXmlTaskletInstance();

        $this->productsCursorMock->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn(null);

        $this->productBuilderMock->expects($this->atLeastOnce())
            ->method('createProduct')
            ->willReturn($product);

        $this->tradeItemProcessorMock->expects($this->exactly(3))
            ->method('setProductToUpdate');

        $this->productSaverMock->expects($this->atLeastOnce())
            ->method('save');

        $importTasklet->execute();
    }

    /** @dataProvider dataProcess */
    public function testProcessForAnExistingProduct(ProductInterface $product): void
    {
        $importTasklet = $this->getE2OpenFromXmlTaskletInstance();

        $this->productsCursorMock->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn($product);

        $this->tradeItemProcessorMock->expects($this->atLeastOnce())->method('processNode');

        $this->tradeItemProcessorMock->expects($this->exactly(3))
            ->method('setProductToUpdate');

        $this->productSaverMock->expects($this->atLeastOnce())
            ->method('save');

        $importTasklet->execute();
    }

    public function dataProcessForDifferentFamily(): array
    {
        $family = (new FamilyBuilder())->withCode('xxxsss')->build();
        $product = (new \PcmtCoreBundle\Tests\TestDataBuilder\ProductBuilder())->withFamily($family)->build();

        return [
            [$product],
        ];
    }

    /** @dataProvider dataProcessForDifferentFamily */
    public function testProcessForDifferentFamily(ProductInterface $product): void
    {
        $importTasklet = $this->getE2OpenFromXmlTaskletInstance();

        $this->productsCursorMock->expects($this->atLeastOnce())
            ->method('current')
            ->willReturn($product);

        $this->tradeItemProcessorMock->expects($this->never())->method('processNode');

        $this->productSaverMock->expects($this->never())->method('save');

        $importTasklet->execute();
    }

    public function getE2OpenFromXmlTaskletInstance(): TaskletInterface
    {
        $tasklet = new E2OpenFromXmlTasklet(
            $this->productSaverMock,
            $this->productBuilderMock,
            $this->tradeItemProcessorMock,
            $this->productQueryBuilderFactoryMock,
            $this->loggerMock
        );
        $tasklet->setStepExecution($this->stepExecutionMock);

        return $tasklet;
    }
}
