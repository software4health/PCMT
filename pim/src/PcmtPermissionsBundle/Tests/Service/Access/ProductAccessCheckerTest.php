<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Service\Access;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepository;
use PcmtPermissionsBundle\Service\Access\ProductAccessChecker;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductAccessCheckerTest extends TestCase
{
    /** @var ProductAccessChecker */
    private $accessChecker;

    /** @var CategoryAccessRepository|MockObject */
    private $categoryAccessRepositoryMock;

    /** @var CategoryAwareInterface|MockObject */
    private $entityMock;

    protected function setUp(): void
    {
        $this->categoryAccessRepositoryMock = $this->createMock(CategoryAccessRepository::class);
        $this->entityMock = $this->createMock(CategoryAwareInterface::class);
        $this->accessChecker = new ProductAccessChecker($this->categoryAccessRepositoryMock);
    }

    public function dataCheckForUser(): array
    {
        $categories = new ArrayCollection();
        $categories->add((new CategoryBuilder())->build());

        $userGroup = (new UserGroupBuilder())->build();
        $userGroups = [$userGroup];

        $categoryAccess = (new CategoryAccessBuilder())
            ->withAccessLevel(CategoryAccess::EDIT_LEVEL)
            ->withUserGroup($userGroup)
            ->build();
        $accesses = [$categoryAccess];

        return [
            'categories null'                                         => [null, [], [], true],
            'categories empty'                                        => [new ArrayCollection(), [], [], true],
            'categories exist, no accesses defined'                   => [$categories, [], [], true],
            'categories exist, accesses defined, no user groups'      => [$categories, $accesses, [], false],
            'categories exist, accesses defined, user groups defined' => [$categories, $accesses, $userGroups, true],
        ];
    }

    /**
     * @dataProvider dataCheckForUser
     */
    public function testCheckForUser(?ArrayCollection $categories, array $accesses, array $userGroups, bool $expectedResult): void
    {
        $this->entityMock->method('getCategories')->willReturn($categories);
        $this->categoryAccessRepositoryMock->method('findBy')->willReturn($accesses);
        $user = (new UserBuilder())->withGroups($userGroups)->build();
        $this->assertEquals($expectedResult, $this->accessChecker->checkForUser($this->entityMock, $user));
    }
}