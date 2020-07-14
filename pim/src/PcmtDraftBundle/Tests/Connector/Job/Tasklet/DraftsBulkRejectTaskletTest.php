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
use PcmtDraftBundle\Connector\Job\Tasklet\DraftsBulkRejectTasklet;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Draft\DraftFacade;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class DraftsBulkRejectTaskletTest extends TestCase
{
    /** @var DraftsBulkRejectTasklet */
    private $draftBulkRejectTasklet;

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

        $this->draftBulkRejectTasklet = new DraftsBulkRejectTasklet(
            $this->draftFacade,
            $this->normalizer,
            $this->draftRepository
        );
    }

    public function testExecuteWhenAllSelectedAndNoOneExcluded(): void
    {
        $this->draftBulkRejectTasklet->setStepExecution($this->stepExecution);

        $this->stepExecution
            ->method('getJobParameters')
            ->willReturn($this->jobInstance);

        $this->jobInstance
            ->method('get')
            ->withConsecutive(['allSelected'], ['excluded'], ['selected'])
            ->willReturnOnConsecutiveCalls(true, [], []);

        $draftOfANewProduct = (new NewProductDraftBuilder())->build();
        $draftOfAnExistingProduct = (new ExistingProductDraftBuilder())->build();

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
            ->method('rejectDraft')
            ->withConsecutive([$draftOfANewProduct], [$draftOfAnExistingProduct]);

        $this->draftBulkRejectTasklet->execute();
    }

    public function testExecuteWhenAllSelectedAndOneDraftIsExcluded(): void
    {
        $this->draftBulkRejectTasklet->setStepExecution($this->stepExecution);

        $this->stepExecution
            ->method('getJobParameters')
            ->willReturn($this->jobInstance);

        $this->jobInstance
            ->method('get')
            ->withConsecutive(['allSelected'], ['excluded'], ['selected'])
            ->willReturnOnConsecutiveCalls(true, [31], []);

        $draftOfANewProduct = (new NewProductDraftBuilder())
            ->withId(30)
            ->build();

        $draftOfAnExistingProduct = (new ExistingProductDraftBuilder())
            ->withId(31)
            ->build();

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
            ->method('rejectDraft')
            ->withConsecutive([$draftOfANewProduct], [$draftOfAnExistingProduct]);

        $this->draftBulkRejectTasklet->execute();
    }

    public function testExecuteWhenSelectedSomeDrafts(): void
    {
        $this->draftBulkRejectTasklet->setStepExecution($this->stepExecution);

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

        $draftOfANewProduct = (new NewProductDraftBuilder())
            ->withId(30)
            ->build();

        $draftOfAnExistingProduct = (new ExistingProductDraftBuilder())
            ->withId(31)
            ->build();

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
            ->method('rejectDraft')
            ->withConsecutive([$draftOfANewProduct], [$draftOfAnExistingProduct]);

        $this->draftBulkRejectTasklet->execute();
    }

    public function testExecuteWhenThereIsNoStepExecution(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->draftBulkRejectTasklet->execute();
    }

    public function testExecuteWhenDraftApprovingFailed(): void
    {
        $this->draftBulkRejectTasklet->setStepExecution($this->stepExecution);

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

        $draftOfANewProduct = (new NewProductDraftBuilder())
            ->withId(30)
            ->build();

        $draftOfAnExistingProduct = (new ExistingProductDraftBuilder())
            ->withId(31)
            ->build();

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
            'draft_rejection',
            'no'
        );
        $violations = new ConstraintViolationList();
        $violations->add($violation);
        $exception->method('getViolations')->willReturn($violations);

        $this->draftFacade
            ->expects($this->exactly(2))
            ->method('rejectDraft')
            ->willThrowException($exception);

        $this->stepExecution
            ->expects($this->exactly(2))
            ->method('addWarning');

        $this->draftBulkRejectTasklet->execute();
    }
}