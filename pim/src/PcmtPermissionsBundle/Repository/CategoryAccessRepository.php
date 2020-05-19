<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Repository;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\ORM\EntityRepository;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;

class CategoryAccessRepository extends EntityRepository implements CategoryAccessRepositoryInterface
{
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

        return $categoryWithAccess;
    }
}