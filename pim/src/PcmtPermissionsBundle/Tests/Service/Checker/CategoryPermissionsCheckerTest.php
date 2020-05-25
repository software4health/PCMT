<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Service\Checker;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepositoryInterface;
use PcmtPermissionsBundle\Service\Checker\CategoryPermissionsChecker;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryPermissionsCheckerTest extends TestCase
{
    /** @var TokenStorageInterface|MockObject */
    private $tokenStorageMock;

    /** @var CategoryAccessRepositoryInterface|MockObject */
    private $accessRepositoryMock;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** @var string[] */
    private $accessesToTest = [];

    protected function setUp(): void
    {
        $this->accessesToTest = [
            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
            CategoryPermissionsCheckerInterface::OWN_LEVEL,
        ];
        $this->tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $this->accessRepositoryMock = $this->createMock(CategoryAccessRepositoryInterface::class);
        $this->categoryPermissionsChecker = new CategoryPermissionsChecker(
            $this->tokenStorageMock,
            $this->accessRepositoryMock
        );
    }

    public function testExceptionIsThrownWhenCheckingUnhandledAccessRights(): void
    {
        $user = (new UserBuilder())->build();
        $productCategoriesCollection = new ArrayCollection();
        $productCategoriesCollection->add((new CategoryBuilder())->build());
        $categoryWithAccess =
            (new CategoryWithAccessBuilder())
                ->withAccessesForGroup(
                    [
                        CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                        CategoryPermissionsCheckerInterface::OWN_LEVEL,
                    ],
                    (new UserGroupBuilder())->build()
                )
                ->build();

        $this->accessRepositoryMock->method('getCategoryWithAccess')->willReturn($categoryWithAccess);
        $entityMock = $this->createMock(CategoryAwareInterface::class);
        $entityMock->method('getCategories')->willReturn($productCategoriesCollection);

        $this->expectException(ParameterNotFoundException::class);
        $this->categoryPermissionsChecker->hasAccessToProduct('unhandle_sad_type', $entityMock, $user);
    }

    public function testUserIsTakenFromTokenStorageWhenCheckingAccessRights(): void
    {
        $user = (new UserBuilder())->build();
        $productCategoriesCollection = new ArrayCollection();
        $productCategoriesCollection->add((new CategoryBuilder())->build());
        $categoryWithAccess = (new CategoryWithAccessBuilder())->build();

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $this->tokenStorageMock
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenMock);
        $this->accessRepositoryMock->method('getCategoryWithAccess')->willReturn($categoryWithAccess);
        $entityMock = $this->createMock(CategoryAwareInterface::class);
        $entityMock->method('getCategories')->willReturn($productCategoriesCollection);

        $this->categoryPermissionsChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::VIEW_LEVEL, $entityMock);
    }

    /**
     * @dataProvider dataHasAlwaysAccessToProduct
     */
    public function testHasAlwaysAccessToProduct(
        ?ArrayCollection $productCategories,
        ?CategoryWithAccess $categoryWithAccess
    ): void {
        $this->accessRepositoryMock->method('getCategoryWithAccess')->willReturn($categoryWithAccess);
        $entityMock = $this->createMock(CategoryAwareInterface::class);
        $entityMock->method('getCategories')->willReturn($productCategories);
        $user = (new UserBuilder())->build();

        foreach ($this->accessesToTest as $accessType) {
            $result = $this->categoryPermissionsChecker->hasAccessToProduct($accessType, $entityMock, $user);
            $this->assertTrue($result);
        }
    }

    public function dataHasAlwaysAccessToProduct(): array
    {
        $productCategoriesCollection = new ArrayCollection();
        $productCategoriesCollection->add((new CategoryBuilder())->build());

        return [
            'product without categories' => [
                null,
                null,
            ],
            'category without set accesses' => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->build(),
            ],
        ];
    }

    /**
     * @dataProvider dataHasAccessToProductForSpecificUser
     */
    public function testHasAccessToProductForSpecificUser(
        ?ArrayCollection $productCategories,
        ?CategoryWithAccess $categoryWithAccess,
        array $accessTypesWithPermissions
    ): void {
        $this->accessRepositoryMock->method('getCategoryWithAccess')->willReturn($categoryWithAccess);
        $entityMock = $this->createMock(CategoryAwareInterface::class);
        $entityMock->method('getCategories')->willReturn($productCategories);
        $user = (new UserBuilder())->build();

        foreach ($this->accessesToTest as $accessType) {
            $result = $this->categoryPermissionsChecker->hasAccessToProduct($accessType, $entityMock, $user);
            $expectedResult = in_array($accessType, $accessTypesWithPermissions);
            $this->assertSame($expectedResult, $result);
        }
    }

    public function dataHasAccessToProductForSpecificUser(): array
    {
        $productCategoriesCollection = new ArrayCollection();
        $productCategoriesCollection->add((new CategoryBuilder())->build());

        return [
            'View and Own rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                            CategoryPermissionsCheckerInterface::OWN_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
            ],
            'Own rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::OWN_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                    CategoryPermissionsCheckerInterface::OWN_LEVEL,
                ],
            ],
            'View and Edit rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
            ],
            'Edit rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                    CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                ],
            ],
            'View rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                ],
            ],
            'No rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->build(),
                [],
            ],
        ];
    }
}