<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Service\Checker;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepositoryInterface;
use PcmtPermissionsBundle\Service\Checker\CategoryPermissionsChecker;
use PcmtPermissionsBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\CategoryWithAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
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
            CategoryAccess::VIEW_LEVEL,
            CategoryAccess::EDIT_LEVEL,
            CategoryAccess::OWN_LEVEL,
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
                        CategoryAccess::VIEW_LEVEL,
                        CategoryAccess::OWN_LEVEL,
                    ],
                    (new UserGroupBuilder())->build()
                )
                ->build();

        $this->accessRepositoryMock->method('getCategoryWithAccess')->willReturn($categoryWithAccess);
        $productMock = $this->createMock(ProductInterface::class);
        $productMock->method('getCategories')->willReturn($productCategoriesCollection);

        $this->expectException(ParameterNotFoundException::class);
        $this->categoryPermissionsChecker->hasAccessToProduct('unhandle_sad_type', $productMock, $user);
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
        $productMock = $this->createMock(ProductInterface::class);
        $productMock->method('getCategories')->willReturn($productCategoriesCollection);

        $this->categoryPermissionsChecker->hasAccessToProduct(CategoryAccess::VIEW_LEVEL, $productMock);
    }

    /**
     * @dataProvider dataHasAlwaysAccessToProduct
     */
    public function testHasAlwaysAccessToProduct(
        ?ArrayCollection $productCategories,
        ?CategoryWithAccess $categoryWithAccess
    ): void {
        $this->accessRepositoryMock->method('getCategoryWithAccess')->willReturn($categoryWithAccess);
        $productMock = $this->createMock(ProductInterface::class);
        $productMock->method('getCategories')->willReturn($productCategories);
        $user = (new UserBuilder())->build();

        foreach ($this->accessesToTest as $accessType) {
            $result = $this->categoryPermissionsChecker->hasAccessToProduct($accessType, $productMock, $user);
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
        $productMock = $this->createMock(ProductInterface::class);
        $productMock->method('getCategories')->willReturn($productCategories);
        $user = (new UserBuilder())->build();

        foreach ($this->accessesToTest as $accessType) {
            $result = $this->categoryPermissionsChecker->hasAccessToProduct($accessType, $productMock, $user);
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
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::VIEW_LEVEL,
                            CategoryAccess::OWN_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryAccess::VIEW_LEVEL,
                    CategoryAccess::EDIT_LEVEL,
                    CategoryAccess::OWN_LEVEL,
                ],
            ],
            'Own rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::OWN_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryAccess::VIEW_LEVEL,
                    CategoryAccess::EDIT_LEVEL,
                    CategoryAccess::OWN_LEVEL,
                ],
            ],
            'View and Edit rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::VIEW_LEVEL,
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryAccess::VIEW_LEVEL,
                    CategoryAccess::EDIT_LEVEL,
                ],
            ],
            'Edit rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryAccess::VIEW_LEVEL,
                    CategoryAccess::EDIT_LEVEL,
                ],
            ],
            'View rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::VIEW_LEVEL,
                        ],
                        (new UserGroupBuilder())->build()
                    )
                    ->build(),
                [
                    CategoryAccess::VIEW_LEVEL,
                ],
            ],
            'No rights'  => [
                $productCategoriesCollection,
                (new CategoryWithAccessBuilder())
                    ->clearAccesses()
                    ->withAccessesForGroup(
                        [
                            CategoryAccess::EDIT_LEVEL,
                        ],
                        (new UserGroupBuilder())->buildWithAnotherId()
                    )
                    ->build(),
                [],
            ],
        ];
    }
}