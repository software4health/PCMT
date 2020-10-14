<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtRulesBundle\Connector\Job\Step\RuleProcessStep;
use PcmtRulesBundle\Repository\RuleRepository;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProcessor;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleProcessStepTest extends TestCase
{
    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var JobRepositoryInterface|MockObject */
    private $jobRepositoryMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var RuleRepository|MockObject */
    private $ruleRepositoryMock;

    /** @var RuleAttributeProvider|MockObject */
    private $attributeProviderMock;

    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $pqbFactoryMock;

    /** @var ProductQueryBuilderInterface|MockObject */
    private $productQueryBuilderMock;

    /** @var RuleProcessor|MockObject */
    private $ruleProductProcessorMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->stepExecutionMock->method('getJobParameters')->willReturn($this->jobParametersMock);
        $this->ruleRepositoryMock = $this->createMock(RuleRepository::class);
        $this->attributeProviderMock = $this->createMock(RuleAttributeProvider::class);
        $this->pqbFactoryMock = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);
        $this->pqbFactoryMock->method('create')->willReturn($this->productQueryBuilderMock);
        $this->ruleProductProcessorMock = $this->createMock(RuleProcessor::class);
    }

    /**
     * @dataProvider dataDoExecute
     */
    public function testDoExecute(array $products, int $expectedCalls): void
    {
        $rule = (new RuleBuilder())->build();
        $this->ruleRepositoryMock->expects($this->once())->method('find')->willReturn($rule);

        $this->productQueryBuilderMock->method('execute')->willReturnOnConsecutiveCalls($products, []);

        $class = new \ReflectionClass(RuleProcessStep::class);
        $method = $class->getMethod('doExecute');
        $method->setAccessible(true);

        $step = $this->getRuleProcessStepInstance();

        $this->ruleProductProcessorMock->expects($this->exactly($expectedCalls))->method('process');

        $method->invokeArgs($step, [$this->stepExecutionMock]);
    }

    public function dataDoExecute(): array
    {
        $product1 = (new ProductBuilder())->build();
        $product2 = (new ProductBuilder())->build();
        $productModel1 = (new ProductModelBuilder())->build();
        $productModel2 = (new ProductModelBuilder())->addSubProductModel($productModel1)->addProductVariant($product2)->build();

        return [
            [[], 0],
            [[$product1, $productModel2], 4],
        ];
    }

    private function getRuleProcessStepInstance(): RuleProcessStep
    {
        $step = new RuleProcessStep(
            'name',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock
        );
        $step->setRuleRepository($this->ruleRepositoryMock);
        $step->setAttributeProvider($this->attributeProviderMock);
        $step->setPqbFactory($this->pqbFactoryMock);
        $step->setRuleProductProcessor($this->ruleProductProcessorMock);

        return $step;
    }
}