<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Updater;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepository;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PcmtPermissionsBundle\Updater\CategoryChildrenPermissionsUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryChildrenPermissionUpdaterTest extends TestCase
{
    /** @var CategoryAccessRepository|MockObject */
    private $accessRepositoryMock;

    /** @var SaverInterface|MockObject */
    private $categoryWithAccessSaverMock;

    /** @var CategoryChildrenPermissionsUpdater */
    private $updater;

    protected function setUp(): void
    {
        $this->accessRepositoryMock = $this->createMock(CategoryAccessRepository::class);
        $this->categoryWithAccessSaverMock = $this->createMock(SaverInterface::class);

        $this->updater = new CategoryChildrenPermissionsUpdater($this->accessRepositoryMock, $this->categoryWithAccessSaverMock);
    }

    public function dataUpdate(): array
    {
        $categoryWithAccess = (new CategoryWithAccessBuilder())->withTwoChildren()->build();

        return [
            'normal accesses' => [(new CategoryWithAccessBuilder())->withTwoChildren()->build(), 2],
            'no children'     => [(new CategoryWithAccessBuilder())->withChildren([$categoryWithAccess])->build(), 3],
            'no accesses'     => [(new CategoryWithAccessBuilder())->withTwoChildren()->withAccesses([])->build(), 0],
        ];
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(CategoryWithAccess $categoryWithAccess, int $expectedSaves): void
    {
        $this->accessRepositoryMock->expects($this->atLeastOnce())->method('findBy')->willReturn([]);

        $this->categoryWithAccessSaverMock->expects($this->exactly($expectedSaves))->method('save');

        $this->updater->update($categoryWithAccess);
    }
}