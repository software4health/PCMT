<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Connector\Job\Tasklet;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtDraftBundle\Connector\Job\Tasklet\DraftsBulkApproveTasklet;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Draft\DraftFacade;
use PcmtDraftBundle\Tests\TestDataBuilder\DraftBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class DraftsBulkApproveTaskletTest extends TestCase
{
    /** @var DraftsBulkApproveTasklet */
    private $draftBulkApproveTasklet;

    /** @var DraftFacade|MockObject */
    private $draftFacade;

    /** @var NormalizerInterface|MockObject */
    private $normalizer;

    /** @var DraftRepository|MockObject */
    private $draftRepository;

    /** @var StepExecution|MockObject */
    private $stepExecution;

    /** @var JobParameters|MockObject */
    private $jobInstance;

    protected function setUp(): void
    {
        $this->draftFacade = $this->createMock(DraftFacade::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->draftRepository = $this->createMock(DraftRepository::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->jobInstance = $this->createMock(JobParameters::class);

        $this->draftBulkApproveTasklet = new DraftsBulkApproveTasklet(
            $this->draftFacade,
            $this->normalizer,
            $this->draftRepository
        );
    }

    public function testExecuteWhenAllSelectedAndNoOneExcluded(): void
    {
        $this->draftBulkApproveTasklet->setStepExecution($this->stepExecution);

        $this->stepExecution
            ->method('getJobParameters')
            ->willReturn($this->jobInstance);

        $this->jobInstance
            ->method('get')
            ->withConsecutive(['allSelected'], ['excluded'], ['selected'])
            ->willReturnOnConsecutiveCalls(true, [], []);

        $draftOfANewProduct = (new DraftBuilder())->buildDraftOfANewProduct();
        $draftOfAnExistingProduct = (new DraftBuilder())->buildDraftOfAnExistingProduct();

        $drafts = [
            $draftOfANewProduct,
            $draftOfAnExistingProduct,
        ];

        $this->draftRepository
            ->method('findBy')
            ->with(['status' => AbstractDraft::STATUS_NEW])
            ->willReturn($drafts);

        $this->draftFacade
            ->expects($this->exactly(2))
            ->method('approveDraft')
            ->withConsecutive([$draftOfANewProduct], [$draftOfAnExistingProduct]);

        $this->draftBulkApproveTasklet->execute();
    }

    public function testExecuteWhenAllSelectedAndOneDraftIsExcluded(): void
    {
        $this->draftBulkApproveTasklet->setStepExecution($this->stepExecution);

        $this->stepExecution
            ->method('getJobParameters')
            ->willReturn($this->jobInstance);

        $this->jobInstance
            ->method('get')
            ->withConsecutive(['allSelected'], ['excluded'], ['selected'])
            ->willReturnOnConsecutiveCalls(true, [31], []);

        $draftOfANewProduct = (new DraftBuilder())
            ->withId(30)
            ->buildDraftOfANewProduct();

        $draftOfAnExistingProduct = (new DraftBuilder())
            ->withId(31)
            ->buildDraftOfAnExistingProduct();

        $drafts = [
            $draftOfANewProduct,
            $draftOfAnExistingProduct,
        ];

        $this->draftRepository
            ->method('findBy')
            ->with(['status' => AbstractDraft::STATUS_NEW])
            ->willReturn($drafts);

        $this->draftFacade
            ->expects($this->exactly(1))
            ->method('approveDraft')
            ->withConsecutive([$draftOfANewProduct], [$draftOfAnExistingProduct]);

        $this->draftBulkApproveTasklet->execute();
    }

    public function testExecuteWhenSelectedSomeDrafts(): void
    {
        $this->draftBulkApproveTasklet->setStepExecution($this->stepExecution);

        $this->stepExecution
            ->method('getJobParameters')
            ->willReturn($this->jobInstance);

        $this->jobInstance
            ->method('get')
            ->withConsecutive(['allSelected'], ['excluded'], ['selected'])
            ->willReturnOnConsecutiveCalls(false, [], [
                30,
                31,
            ]);

        $draftOfANewProduct = (new DraftBuilder())
            ->withId(30)
            ->buildDraftOfANewProduct();

        $draftOfAnExistingProduct = (new DraftBuilder())
            ->withId(31)
            ->buildDraftOfAnExistingProduct();

        $drafts = [
            $draftOfANewProduct,
            $draftOfAnExistingProduct,
        ];

        $this->draftRepository
            ->method('findBy')
            ->with(
                [
                    'status' => AbstractDraft::STATUS_NEW,
                    'id'     => [
                        30,
                        31,
                    ],
                ]
            )
            ->willReturn($drafts);

        $this->draftFacade
            ->expects($this->exactly(2))
            ->method('approveDraft')
            ->withConsecutive([$draftOfANewProduct], [$draftOfAnExistingProduct]);

        $this->draftBulkApproveTasklet->execute();
    }

    public function testExecuteWhenThereIsNoStepExecution(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->draftBulkApproveTasklet->execute();
    }

    public function testExecuteWhenDraftApprovingFailed(): void
    {
        $this->draftBulkApproveTasklet->setStepExecution($this->stepExecution);

        $this->stepExecution
            ->method('getJobParameters')
            ->willReturn($this->jobInstance);

        $this->jobInstance
            ->method('get')
            ->withConsecutive(['allSelected'], ['excluded'], ['selected'])
            ->willReturnOnConsecutiveCalls(false, [], [
                30,
                31,
            ]);

        $draftOfANewProduct = (new DraftBuilder())
            ->withId(30)
            ->buildDraftOfANewProduct();

        $draftOfAnExistingProduct = (new DraftBuilder())
            ->withId(31)
            ->buildDraftOfAnExistingProduct();

        $drafts = [
            $draftOfANewProduct,
            $draftOfAnExistingProduct,
        ];

        $this->draftRepository
            ->method('findBy')
            ->with(
                [
                    'status' => AbstractDraft::STATUS_NEW,
                    'id'     => [
                        30,
                        31,
                    ],
                ]
            )
            ->willReturn($drafts);

        $exception = $this->createMock(DraftViolationException::class);
        $violation = new ConstraintViolation(
            'No corresponding object found.',
            'No corresponding object found.',
            [],
            $draftOfANewProduct,
            'draft_approval',
            'no'
        );
        $violations = new ConstraintViolationList();
        $violations->add($violation);
        $exception->method('getViolations')->willReturn($violations);

        $this->draftFacade
            ->expects($this->exactly(2))
            ->method('approveDraft')
            ->willThrowException($exception);

        $this->stepExecution
            ->expects($this->exactly(2))
            ->method('addWarning');

        $this->draftBulkApproveTasklet->execute();
    }
}