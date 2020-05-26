<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\Updater;

use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use PcmtPermissionsBundle\Tests\TestDataBuilder\AttributeGroupWithAccessBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\UserGroupBuilder;
use PcmtPermissionsBundle\Updater\AttributeGroupUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeGroupUpdaterTest extends TestCase
{
    /** @var AttributeGroupUpdater */
    private $attributeGroupUpdater;

    /** @var IdentifiableObjectRepositoryInterface|MockObject */
    private $attributeRepositoryMock;

    /** @var AttributeGroupRepositoryInterface|MockObject */
    private $attributeGroupRepositoryMock;

    /** @var TranslatableUpdater|MockObject */
    private $translatableUpdaterMock;

    /** @var GroupRepositoryInterface|MockObject */
    private $userGroupRepositoryMock;

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(
            IdentifiableObjectRepositoryInterface::class
        );
        $this->attributeGroupRepositoryMock = $this->createMock(
            AttributeGroupRepositoryInterface::class
        );
        $this->translatableUpdaterMock = $this->createMock(TranslatableUpdater::class);
        $this->userGroupRepositoryMock = $this->createMock(GroupRepository::class);

        $this->attributeGroupUpdater = new AttributeGroupUpdater(
            $this->attributeRepositoryMock,
            $this->attributeGroupRepositoryMock,
            $this->translatableUpdaterMock
        );
        $this->attributeGroupUpdater
            ->setUserGroupRepository($this->userGroupRepositoryMock);
    }

    public function testUpdate(): void
    {
        $attributeGroup = (new AttributeGroupWithAccessBuilder())->build();

        $data = [
            'permission[allowed_to_edit]' => '1',
            'permission[allowed_to_view]' => '1,2',
            'permission[allowed_to_own]'  => '1',
        ];

        $userGroup1 = (new UserGroupBuilder())->withId(1)->build();
        $userGroup2 = (new UserGroupBuilder())->withId(2)->build();

        $this->userGroupRepositoryMock
            ->method('findBy')
            ->withConsecutive(
                [
                    [
                        'id' => ['1'],
                    ],
                ],
                [
                    [
                        'id' => ['1', '2'],
                    ],
                ],
                [
                    [
                        'id' => ['1'],
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                [$userGroup1],
                [$userGroup1, $userGroup2],
                [$userGroup1]
            );

        $this->attributeGroupUpdater
            ->update($attributeGroup, $data);

        $this->assertEquals(4, $attributeGroup->getAccesses()->count());
    }
}
