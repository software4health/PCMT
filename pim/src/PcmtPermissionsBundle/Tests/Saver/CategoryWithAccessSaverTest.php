<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepository;
use PcmtPermissionsBundle\Saver\CategoryWithAccessSaver;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryWithAccessSaverTest extends TestCase
{
    /** @var SaverInterface|MockObject */
    private $categorySaverMock;

    /** @var SaverInterface|MockObject */
    private $categoryAccessSaverMock;

    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var CategoryAccessRepository|MockObject */
    private $categoryAccessRepositoryMock;

    /** @var CategoryWithAccessSaver */
    private $categoryWithAccessSaver;

    protected function setUp(): void
    {
        $this->categorySaverMock = $this->createMock(SaverInterface::class);
        $this->categoryAccessSaverMock = $this->createMock(SaverInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->categoryAccessRepositoryMock = $this->createMock(CategoryAccessRepository::class);
        $this->categoryWithAccessSaver = new CategoryWithAccessSaver(
            $this->categorySaverMock,
            $this->categoryAccessSaverMock,
            $this->entityManagerMock,
            $this->categoryAccessRepositoryMock
        );
    }

    public function dataSave(): array
    {
        $access = (new CategoryAccessBuilder())->build();

        return [
            'normal accesses' => [(new CategoryWithAccessBuilder())->build(), []],
            'no accesses'     => [(new CategoryWithAccessBuilder())->withAccesses([])->build(), [$access]],
        ];
    }

    /**
     * @dataProvider dataSave
     */
    public function testSave(CategoryWithAccess $categoryWithAccess, iterable $repositoryResult): void
    {
        $this->categoryAccessRepositoryMock->method('findBy')->willReturn($repositoryResult);
        $this->entityManagerMock->expects($this->exactly(count($repositoryResult)))->method('remove');

        $this->categorySaverMock->expects($this->once())->method('save');
        $this->categoryAccessSaverMock->expects($this->exactly(count($categoryWithAccess->getAccesses())))->method('save');

        $this->categoryWithAccessSaver->save($categoryWithAccess);
    }
}