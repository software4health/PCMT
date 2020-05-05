<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\UserManagement\Component\Model\Group;
use PcmtPermissionsBundle\Entity\CategoryAccess;

class CategoryAccessBuilder
{
    /** @var CategoryAccess */
    private $categoryAccess;

    public function __construct()
    {
        $category = new Category();
        $userGroup = new Group();
        $this->categoryAccess = new CategoryAccess($category, $userGroup, CategoryAccess::VIEW_LEVEL);
    }

    public function build(): CategoryAccess
    {
        return $this->categoryAccess;
    }
}