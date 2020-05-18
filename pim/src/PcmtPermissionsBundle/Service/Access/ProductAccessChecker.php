<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Service\Access;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Repository\CategoryAccessRepository;
use PcmtSharedBundle\Service\Access\ProductAccessCheckerInterface;

class ProductAccessChecker implements ProductAccessCheckerInterface
{
    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    /** @var ArrayCollection */
    private $groups;

    public function __construct(CategoryAccessRepository $categoryAccessRepository)
    {
        $this->categoryAccessRepository = $categoryAccessRepository;
    }

    public function checkForUser(CategoryAwareInterface $product, UserInterface $user): bool
    {
        $this->groups = $user->getGroups();

        $categories = $product->getCategories();
        if (!$categories || count($categories) < 1) {
            // if no categories set, user has access
            return true;
        }

        foreach ($categories as $category) {
            // give access to product if at least one category has access
            if ($this->checkAccessForCategory($category)) {
                return true;
            }
        }

        return false;
    }

    private function getAccessesForCategory(CategoryInterface $category): array
    {
        return $this->categoryAccessRepository->findBy(['category' => $category]);
    }

    private function checkAccessForCategory(CategoryInterface $category): bool
    {
        $accesses = $this->getAccessesForCategory($category);
        if (!$accesses) {
            // grant access if no user groups defined
            return true;
        }

        foreach ($accesses as $access) {
            /** @var CategoryAccess $access */
            foreach ($this->groups as $group) {
                /** @var Group $group */
                if ($access->getUserGroup()->getId() === $group->getId()) {
                    $levels = [
                        CategoryAccess::EDIT_LEVEL,
                        CategoryAccess::OWN_LEVEL,
                    ];
                    if (in_array($access->getLevel(), $levels)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}