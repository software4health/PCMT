<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\Draft\DraftApprover;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DraftApproverTest extends TestCase
{
    /** @var GeneralObjectFromDraftCreator */
    private $creatorMock;

    /** @var EntityManagerInterface */
    private $entityManagerMock;

    /** @var TokenStorageInterface */
    private $tokenStorageMock;

    /** @var ValidatorInterface */
    private $validatorMock;

    /** @var SaverInterface */
    private $saverMock;

    protected function setUp(): void
    {
        $this->creatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $this->tokenStorageMock->method('getToken')->willReturn($token);

        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->saverMock = $this->createMock(SaverInterface::class);

        parent::setUp();
    }

    /**
     * @dataProvider dataApprove
     */
    public function testApprove(DraftInterface $draft): void
    {
        $objectToSave = $this->createMock(EntityWithAssociationsInterface::class);
        $this->creatorMock->expects($this->once())->method('getObjectToSave')->willReturn($objectToSave);
        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->entityManagerMock->expects($this->once())->method('flush');

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(0);
        $this->validatorMock->expects($this->once())->method('validate')->willReturn($violations);

        $service = new DraftApprover($this->entityManagerMock, $this->tokenStorageMock, $this->validatorMock, $this->saverMock, $this->creatorMock);

        /** @var MockObject $draft */
        $draft->expects($this->once())->method('setStatus')->with();

        $service->approve($draft);
    }

    public function dataApprove(): array
    {
        $draft = $this->createMock(DraftInterface::class);

        return [
            [$draft],
        ];
    }

    /**
     * @dataProvider dataApproveNoObject
     */
    public function testApproveNoObject(DraftInterface $draft): void
    {
        $this->creatorMock->expects($this->once())->method('getObjectToSave')->willReturn(null);
        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');

        $this->validatorMock->expects($this->never())->method('validate');

        $this->expectException(\Throwable::class);

        $service = new DraftApprover($this->entityManagerMock, $this->tokenStorageMock, $this->validatorMock, $this->saverMock, $this->creatorMock);

        /** @var MockObject $draft */
        $draft->expects($this->never())->method('setStatus');

        $service->approve($draft);
    }

    public function dataApproveNoObject(): array
    {
        $draft = $this->createMock(DraftInterface::class);

        return [
            [$draft],
        ];
    }
}