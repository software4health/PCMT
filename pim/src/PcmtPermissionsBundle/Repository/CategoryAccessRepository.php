<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Repository;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Doctrine\ORM\EntityRepository;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class CategoryAccessRepository extends EntityRepository implements CategoryAccessRepositoryInterface
{
    /** @var GroupRepository */
    private $userGroupRepository;

    public function setUserGroupRepository(GroupRepository $userGroupRepository): void
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    public function getCategoryWithAccess(CategoryInterface $category): CategoryWithAccess
    {
        $categoryWithAccess = new CategoryWithAccess($category);
        $accesses = $this->findBy(
            [
                'category' => $category,
            ]
        );
        foreach ($accesses as $access) {
            $categoryWithAccess->addAccess($access);
        }
        $levels = [
            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
            CategoryPermissionsCheckerInterface::OWN_LEVEL,
        ];
        foreach ($levels as $level) {
            if (0 === count($categoryWithAccess->getAccessesOfLevel($level))) {
                $allGroup = $this->userGroupRepository->getDefaultUserGroup();
                $categoryAccess = new CategoryAccess($category, $allGroup, $level);
                $categoryWithAccess->addAccess($categoryAccess);
            }
        }

        return $categoryWithAccess;
    }
}