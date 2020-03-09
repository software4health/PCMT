<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PcmtDraftBundle\Entity\AttributeChange;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Saver\ProductThroughDraftUpsertSaver;
use PcmtDraftBundle\Service\Draft\BaseEntityCreatorInterface;
use PcmtDraftBundle\Service\Draft\DraftCreatorInterface;
use PcmtDraftBundle\Tests\TestDataBuilder\DraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductThroughDraftUpsertSaverTest extends TestCase
{
    /** @var SaverInterface|MockObject */
    private $entitySaverMock;

    /** @var NormalizerInterface|MockObject */
    private $standardNormalizerMock;

    /** @var SaverInterface|MockObject */
    private $draftSaverMock;

    /** @var BaseEntityCreatorInterface|MockObject */
    private $baseEntityCreatorMock;

    /** @var DraftCreatorInterface|MockObject */
    private $draftCreatorMock;

    /** @var UserRepositoryInterface|MockObject */
    private $userRepositoryMock;

    /** @var DraftRepository|MockObject */
    private $draftRepositoryMock;

    protected function setUp(): void
    {
        $this->entitySaverMock = $this->createMock(SaverInterface::class);
        $this->standardNormalizerMock = $this->createMock(NormalizerInterface::class);
        $this->draftSaverMock = $this->createMock(SaverInterface::class);
        $this->baseEntityCreatorMock = $this->createMock(BaseEntityCreatorInterface::class);
        $this->draftCreatorMock = $this->createMock(DraftCreatorInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->userRepositoryMock->method('findOneBy')->willReturn(new User());
        $this->draftRepositoryMock = $this->createMock(DraftRepository::class);

        parent::setUp();
    }

    private function getTestedObject(): ProductThroughDraftUpsertSaver
    {
        return new ProductThroughDraftUpsertSaver(
            $this->entitySaverMock,
            $this->standardNormalizerMock,
            $this->draftSaverMock,
            $this->baseEntityCreatorMock,
            $this->draftCreatorMock,
            $this->userRepositoryMock,
            $this->draftRepositoryMock
        );
    }

    public function testSaveObjectExistsNoDraft(): void
    {
        $product = (new ProductBuilder())->withId(11)->build();

        $this->standardNormalizerMock->method('normalize')->willReturn([]);
        $this->draftRepositoryMock->method('findOneBy')->willReturn(null);

        $this->draftSaverMock->expects($this->once())->method('save');

        $saver = $this->getTestedObject();
        $saver->save($product);
    }

    public function testSaveObjectExistsDraftExists(): void
    {
        $product = (new ProductBuilder())->withId(11)->build();

        $this->standardNormalizerMock->method('normalize')->willReturn([]);

        $draft = (new DraftBuilder())->buildDraftOfAnExistingProduct();
        $this->draftRepositoryMock->method('findOneBy')->willReturn($draft);

        $this->draftSaverMock->expects($this->once())->method('save');

        $saver = $this->getTestedObject();
        $saver->save($product);
    }

    public function testSaveObjectNotExists(): void
    {
        $product = (new ProductBuilder())->withId(null)->build();

        $this->standardNormalizerMock->method('normalize')->willReturn([]);

        $this->draftRepositoryMock->method('findOneBy')->willReturn(null);

        $newProduct = (new ProductBuilder())->withId(12)->build();
        $this->baseEntityCreatorMock->expects($this->once())->method('create')->willReturn($newProduct);
        $this->entitySaverMock->expects($this->once())->method('save')->with($newProduct);

        $draft = (new DraftBuilder())->buildDraftOfAnExistingProduct();
        $this->draftCreatorMock->expects($this->once())->method('create')->willReturn($draft);

        $this->draftSaverMock->expects($this->once())->method('save')->with($draft);

        $saver = $this->getTestedObject();
        $saver->save($product);
    }

    public function testSaveWrongObjectClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $saver = $this->getTestedObject();
        $saver->save($this->createMock(AttributeChange::class));
    }
}