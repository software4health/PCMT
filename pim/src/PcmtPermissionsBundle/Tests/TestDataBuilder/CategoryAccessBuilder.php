<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PcmtPermissionsBundle\Entity\CategoryAccess;

class CategoryAccessBuilder
{
    /** @var CategoryInterface */
    private $category;

    /** @var Group */
    private $userGroup;

    /** @var string */
    private $accessLevel;

    public function __construct()
    {
        $this->category = (new CategoryBuilder())->build();
        $this->userGroup = new Group();
        $this->accessLevel = CategoryAccess::VIEW_LEVEL;
    }

    public function withCategory(CategoryInterface $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function withUserGroup(Group $group): self
    {
        $this->userGroup = $group;

        return $this;
    }

    public function withAccessLevel(string $level): self
    {
        $this->accessLevel = $level;

        return $this;
    }

    public function build(): CategoryAccess
    {
        return new CategoryAccess($this->category, $this->userGroup, $this->accessLevel);
    }
}