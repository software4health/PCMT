<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProcessor;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @property ProductQueryBuilderInterface|MockObject productQueryBuilderMock
 */
class RuleProcessorTest extends TestCase
{
    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $productQueryBuilderFactoryMock;

    /** @var RuleAttributeProvider|MockObject */
    private $ruleAttributeProviderMock;

    /** @var SaverInterface */
    private $productSaverMock;

    /** @var SaverInterface */
    private $productModelSaverMock;

    /** @var ProductQueryBuilderInterface|MockObject */
    private $productQueryBuilderMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var ProductBuilderInterface|MockObject */
    private $productBuilderMock;

    /** @var RuleProcessorCopier|MockObject */
    private $ruleProcessorCopierMock;

    protected function setUp(): void
    {
        $this->productQueryBuilderFactoryMock = $this->createMock(ProductQueryBuilderFactory::class);
        $this->ruleAttributeProviderMock = $this->createMock(RuleAttributeProvider::class);
        $this->productSaverMock = $this->createMock(SaverInterface::class);
        $this->productModelSaverMock = $this->createMock(SaverInterface::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->productQueryBuilderFactoryMock->method('create')->willReturn($this->productQueryBuilderMock);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->productBuilderMock = $this->createMock(ProductBuilderInterface::class);
        $this->ruleProcessorCopierMock = $this->createMock(RuleProcessorCopier::class);
    }

    public function dataProcess(): array
    {
        $product1 = (new ProductBuilder())->withId(2222)->build();
        $destinationProducts = [
            $product1,
            (new ProductBuilder())->withId(2223)->build(),
        ];
        $attributes = [
            (new AttributeBuilder())->build(),
        ];
        $rule = (new RuleBuilder())->build();
        $value = ScalarValue::value($rule->getKeyAttribute()->getCode(), 'xxx');
        $product = (new ProductBuilder())->addValue($value)->build();

        $productModel1 = (new ProductModelBuilder())->addProductVariant($product1)->build();
        $productModel2 = (new ProductModelBuilder())->addSubProductModel($productModel1)->build();

        $destinationProductModels = [
            $productModel2,
        ];

        return [
            [$rule, $product, $destinationProducts, $attributes, 2],
            [$rule, $product, $destinationProductModels, $attributes, 3],
        ];
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(Rule $rule, ProductInterface $sourceProduct, array $destinationProducts, array $attributes, int $expectedCalls): void
    {
        $this->productBuilderMock->method('createProduct')->willReturn(
            (new ProductBuilder())->withId(2332)->build()
        );
        $this->ruleAttributeProviderMock->expects($this->once())->method('getAllForFamilies')->willReturn($attributes);
        $this->productQueryBuilderMock->method('execute')->willReturn($destinationProducts);
        $this->ruleProcessorCopierMock->expects($this->exactly($expectedCalls))->method('copy')->willReturn(true);
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $rule, $sourceProduct);
    }

    public function dataProcessNoDestinationProduct(): array
    {
        $attributes = [
            (new AttributeBuilder())->build(),
        ];
        $rule = (new RuleBuilder())->build();
        $value = ScalarValue::value($rule->getKeyAttribute()->getCode(), 'xxx');
        $product = (new ProductBuilder())->addValue($value)->build();

        return [
            [$rule, $product, $attributes],
        ];
    }

    /**
     * @dataProvider dataProcessNoDestinationProduct
     */
    public function testProcessNoDestinationProduct(Rule $rule, ProductInterface $sourceProduct, array $attributes): void
    {
        $this->productBuilderMock->expects($this->once())->method('createProduct')->willReturn(
            (new ProductBuilder())->withId(2332)->build()
        );
        $this->ruleAttributeProviderMock->expects($this->once())->method('getAllForFamilies')->willReturn($attributes);
        $this->productQueryBuilderMock->method('execute')->willReturn([]);
        $this->ruleProcessorCopierMock->expects($this->exactly(1))->method('copy')->willReturn(true);
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $rule, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessException(Rule $rule, ProductInterface $sourceProduct, array $destinationProducts, array $attributes): void
    {
        $this->ruleAttributeProviderMock->expects($this->once())->method('getAllForFamilies')->willReturn($attributes);
        $this->productQueryBuilderMock->method('execute')->willReturn($destinationProducts);
        $this->ruleProcessorCopierMock->method('copy')->willThrowException(new \Exception());
        $this->stepExecutionMock->expects($this->atLeastOnce())->method('addWarning');
        $processor = $this->getRuleProductProcessorInstance();
        $processor->process($this->stepExecutionMock, $rule, $sourceProduct);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcessFilterException(Rule $rule, ProductInterface $sourceProduct, array $destinationProducts, array $attributes): void
    {
        $this->ruleAttributeProviderMock->expects($this->once())->method('getAllForFamilies')->willReturn($attributes);
        $this->productQueryBuilderMock->method('addFilter')->willThrowException(new \Exception());
        $this->expectException(\Throwable::class);
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

    private function getRuleProductProcessorInstance(): RuleProcessor
    {
        return new RuleProcessor(
            $this->productQueryBuilderFactoryMock,
            $this->ruleAttributeProviderMock,
            $this->productSaverMock,
            $this->productModelSaverMock,
            $this->productBuilderMock,
            $this->ruleProcessorCopierMock
        );
    }
}