<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Entity\Attribute;
use PcmtDraftBundle\Entity\DraftRepositoryInterface;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Saver\DraftSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DraftSaverTest extends TestCase
{
    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    /** @var DraftRepositoryInterface|MockObject */
    private $draftRepositoryMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->draftRepositoryMock = $this->createMock(DraftRepositoryInterface::class);
    }

    /**
     * @dataProvider dataSaveExistingDraft
     */
    public function testSaveExistingDraft(ProductDraftInterface $draft): void
    {
        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $saver = $this->getDraftSaverInstance();
        $saver->save($draft);
    }

    public function dataSaveExistingDraft(): array
    {
        $draft1 = $this->createMock(ProductDraftInterface::class);
        $draft1->method('getId')->willReturn(112);

        return [
            [$draft1],
        ];
    }

    /**
     * @dataProvider dataSaveNewDraft
     */
    public function testSaveNewDraft(ProductDraftInterface $draft): void
    {
        $this->draftRepositoryMock->method('checkIfDraftForObjectAlreadyExists')->willReturn(false);

        $this->entityManagerMock->expects($this->once())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $saver = $this->getDraftSaverInstance();
        $saver->save($draft);
    }

    public function dataSaveNewDraft(): array
    {
        $draft1 = $this->createMock(ProductDraftInterface::class);
        $draft1->method('getId')->willReturn(0);
        $object1 = $this->createMock(ProductInterface::class);
        $draft1->method('getObject')->willReturn($object1);

        $draft2 = $this->createMock(ProductDraftInterface::class);
        $draft2->method('getId')->willReturn(0);
        $draft2->method('getObject')->willReturn(null);

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
        $this->draftRepositoryMock->method('checkIfDraftForObjectAlreadyExists')->willReturn(true);

        $this->entityManagerMock->expects($this->never())->method('persist')->with($draft);
        $this->entityManagerMock->expects($this->never())->method('flush');

        $this->eventDispatcherMock->expects($this->never())->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);

        $saver = $this->getDraftSaverInstance();
        $saver->save($draft);
    }

    public function dataSaveNewDraftForObjectThatAlreadyHasADraft(): array
    {
        $draft1 = $this->createMock(ProductDraftInterface::class);
        $draft1->method('getId')->willReturn(0);

        $object1 = $this->createMock(ProductInterface::class);
        $draft1->method('getObject')->willReturn($object1);

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
        return new DraftSaver($this->entityManagerMock, $this->eventDispatcherMock, $this->draftRepositoryMock);
    }
}