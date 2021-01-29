<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Connector\Job\Step;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtRulesBundle\Connector\Job\Step\CopyProductsRuleStep;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CopyProductsRuleStepTest extends TestCase
{
    /** @var MockObject|EventDispatcherInterface */
    private $eventDispatcherMock;

    /** @var JobRepositoryInterface|MockObject */
    private $jobRepositoryMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var FamilyRepository|MockObject */
    private $familyRepositoryMock;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->jobRepositoryMock = $this->createMock(JobRepositoryInterface::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->jobParametersMock->method('all')->willReturn(['sss' => 'sdsfsd']);
        $this->stepExecutionMock->method('getJobParameters')->willReturn($this->jobParametersMock);
        $this->familyRepositoryMock = $this->createMock(FamilyRepository::class);
    }

    public function testDoExecute(): void
    {
        $class = new \ReflectionClass(CopyProductsRuleStep::class);
        $method = $class->getMethod('doExecute');
        $method->setAccessible(true);

        $step = $this->getRuleStepInstance();

        $this->stepExecutionMock->expects($this->once())->method('addSummaryInfo');

        $method->invokeArgs($step, [$this->stepExecutionMock]);
    }

    private function getRuleStepInstance(): CopyProductsRuleStep
    {
        $step = new CopyProductsRuleStep(
            'name',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock
        );
        $step->setFamilyRepository($this->familyRepositoryMock);

        return $step;
    }
}