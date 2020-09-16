<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProductProcessor;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @property ProductQueryBuilderInterface|MockObject productQueryBuilderMock
 */
class RuleProductProcessorTest extends TestCase
{
    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $productQueryBuilderFactoryMock;

    /** @var RuleAttributeProvider|MockObject */
    private $ruleAttributeProviderMock;

    /** @var PropertyCopierInterface|MockObject */
    private $propertyCopierMock;

    /** @var SaverInterface */
    private $productSaverMock;

    /** @var SaverInterface */
    private $productModelSaverMock;

    /** @var ProductQueryBuilderInterface|MockObject */
    private $productQueryBuilderMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    protected function setUp(): void
    {
        $this->productQueryBuilderFactoryMock = $this->createMock(ProductQueryBuilderFactory::class);
        $this->ruleAttributeProviderMock = $this->createMock(RuleAttributeProvider::class);
        $this->propertyCopierMock = $this->createMock(PropertyCopierInterface::class);
        $this->productSaverMock = $this->createMock(SaverInterface::class);
        $this->productModelSaverMock = $this->createMock(SaverInterface::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->productQueryBuilderFactoryMock->method('create')->willReturn($this->productQueryBuilderMock);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
    }

    public function dataProcess(): array
    {
        $productModel = (new ProductModelBuilder())->build();
        $destinationProducts = [
            (new ProductBuilder())->withId(2222)->build(),
            (new ProductBuilder())->withId(2223)->withParent($productModel)->build(),
        ];
        $attributes = [
            (new AttributeBuilder())->build(),
        ];
        $rule = (new RuleBuilder())->build();
        $value = ScalarValue::value($rule->getKeyAttribute()->getCode(), 'xxx');
        $product = (new ProductBuilder())->addValue($value)->build();

        return [
            [$rule, $product, $destinationProducts, $attributes],
        ];
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(Rule $rule, ProductInterface $sourceProduct, array $destinationProducts, array $attributes): void
    {
        $this->ruleAttributeProviderMock->expects($this->once())->method('getForFamilies')->willReturn($attributes);
        $this->productQueryBuilderMock->method('execute')->willReturn($destinationProducts);
        $this->propertyCopierMock->expects($this->exactly(3))->method('copyData');
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $rule, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessException(Rule $rule, ProductInterface $sourceProduct, array $destinationProducts, array $attributes): void
    {
        $this->ruleAttributeProviderMock->expects($this->once())->method('getForFamilies')->willReturn($attributes);
        $this->productQueryBuilderMock->method('execute')->willReturn($destinationProducts);
        $this->propertyCopierMock->method('copyData')->willThrowException(new \Exception());
        $this->stepExecutionMock->expects($this->atLeastOnce())->method('addWarning');
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $rule, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessNoKeyValue(Rule $rule, ProductInterface $sourceProduct, array $destinationProducts, array $attributes): void
    {
        $sourceProduct = (new ProductBuilder())->build();
        $this->productQueryBuilderMock->expects($this->never())->method('execute');
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $rule, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessWarning(Rule $rule, ProductInterface $sourceProduct, array $destinationProducts, array $attributes): void
    {
        $sourceProduct = (new ProductBuilder())->build();
        $this->productQueryBuilderMock->expects($this->never())->method('execute');
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $rule, $sourceProduct);
    }

    private function getRuleProductProcessorInstance(): RuleProductProcessor
    {
        return new RuleProductProcessor(
            $this->productQueryBuilderFactoryMock,
            $this->ruleAttributeProviderMock,
            $this->propertyCopierMock,
            $this->productSaverMock,
            $this->productModelSaverMock
        );
    }
}