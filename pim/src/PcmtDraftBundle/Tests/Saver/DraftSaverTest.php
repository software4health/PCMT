<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Saver;

use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Entity\Attribute;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Saver\DraftSaver;
use PcmtDraftBundle\Service\Draft\DraftExistenceChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DraftSaverTest extends TestCase
{
    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var DraftExistenceChecker|MockObject */
    private $draftExistenceChekerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->draftExistenceChekerMock = $this->createMock(DraftExistenceChecker::class);
    }

    /**
     * @dataProvider dataSaveExistingDraft
     */
    public function testSaveExistingDraft(DraftInterface $draft): void
    {
        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $saver = $this->getDraftSaverInstance();
        $saver->save($draft);
    }

    public function dataSaveExistingDraft(): array
    {
        $draft1 = $this->createMock(ExistingProductDraft::class);
        $draft1->method('getId')->willReturn(112);

        $draft2 = $this->createMock(NewProductModelDraft::class);
        $draft2->method('getId')->willReturn(11);

        return [
            [$draft1],
            [$draft2],
        ];
    }

    /**
     * @dataProvider dataSaveNewDraft
     */
    public function testSaveNewDraft(DraftInterface $draft): void
    {
        $this->draftExistenceChekerMock->method('checkIfDraftForObjectAlreadyExists')->willReturn(false);

        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $saver = $this->getDraftSaverInstance();
        $saver->save($draft);
    }

    public function dataSaveNewDraft(): array
    {
        $draft1 = $this->createMock(ExistingProductModelDraft::class);
        $draft1->method('getId')->willReturn(0);

        $draft2 = $this->createMock(NewProductDraft::class);
        $draft2->method('getId')->willReturn(0);

        return [
            [$draft1],
            [$draft2],
        ];
    }

    /**
     * @dataProvider dataSaveNewDraftForObjectThatAlreadyHasADraft
     */
    public function testSaveNewDraftForObjectThatAlreadyHasADraft(ProductDraftInterface $draft): void
    {
        $this->draftExistenceChekerMock->method('checkIfDraftForObjectAlreadyExists')->willReturn(true);

        $this->entityManagerMock->expects($this->never())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->never())->method('flush');

        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);

        $saver = $this->getDraftSaverInstance();
        $saver->save($draft);
    }

    public function dataSaveNewDraftForObjectThatAlreadyHasADraft(): array
    {
        $draft1 = $this->createMock(ExistingProductDraft::class);
        $draft1->method('getId')->willReturn(0);

        return [
            [$draft1],
        ];
    }

    public function testSaveIncorrectObject(): void
    {
        $object = $this->createMock(Attribute::class);

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');

        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);

        $saver = $this->getDraftSaverInstance();
        $saver->save($object);
    }

    private function getDraftSaverInstance(): DraftSaver
    {
        return new DraftSaver($this->entityManagerMock, $this->eventDispatcherMock, $this->draftExistenceChekerMock);
    }
}