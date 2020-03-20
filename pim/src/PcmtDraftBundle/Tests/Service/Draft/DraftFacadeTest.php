<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Draft;

use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Saver\DraftSaver;
use PcmtDraftBundle\Service\Draft\DraftApprover;
use PcmtDraftBundle\Service\Draft\DraftFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftFacadeTest extends TestCase
{
    /** @var DraftApprover|MockObject */
    private $productDraftApproverMock;

    /** @var DraftApprover|MockObject */
    private $productModelDraftApproverMock;

    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var DraftSaver|MockObject */
    private $draftSaverMock;

    protected function setUp(): void
    {
        $this->productDraftApproverMock = $this->createMock(DraftApprover::class);
        $this->productModelDraftApproverMock = $this->createMock(DraftApprover::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->draftSaverMock = $this->createMock(DraftSaver::class);
    }

    /**
     * @dataProvider dataApproveDraft
     */
    public function testApproveDraft(DraftInterface $draft, string $expectedApprover): void
    {
        $facade = $this->getDraftFacadeInstance();
        $expectedApprover = $this->{$expectedApprover};
        $expectedApprover->expects($this->once())->method('approve')->with($draft);
        $facade->approveDraft($draft);
    }

    public function dataApproveDraft(): array
    {
        return [
            [$this->createMock(ProductDraftInterface::class), 'productDraftApproverMock'],
            [$this->createMock(ProductModelDraftInterface::class), 'productModelDraftApproverMock'],
        ];
    }

    public function testApproveDraftExpectsException(): void
    {
        $this->expectException(\Throwable::class);
        $facade = $this->getDraftFacadeInstance();
        $facade->approveDraft($this->createMock(DraftInterface::class));
    }

    public function testRejectDraft(): void
    {
        $facade = $this->getDraftFacadeInstance();
        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');
        $draft = $this->createMock(DraftInterface::class);
        $draft->expects($this->once())->method('reject');
        $facade->rejectDraft($draft);
    }

    public function testUpdateDraft(): void
    {
        $facade = $this->getDraftFacadeInstance();
        $draft = $this->createMock(DraftInterface::class);
        $this->draftSaverMock->expects($this->once())->method('save')->with($draft);
        $facade->updateDraft($draft);
    }

    public function getDraftFacadeInstance(): DraftFacade
    {
        return new DraftFacade(
            $this->productDraftApproverMock,
            $this->productModelDraftApproverMock,
            $this->entityManagerMock,
            $this->draftSaverMock
        );
    }
}