<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;

class CategoryBuilder
{
    /** @var CategoryInterface */
    private $category;

    public const DEFAULT_ID = 3;

    public function __construct()
    {
        $this->category = new Category();
        $this->withId(self::DEFAULT_ID);
    }

    public function withId(int $id): self
    {
        $reflection = new \ReflectionClass(get_class($this->category));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->category, $id);

        return $this;
    }

    public function build(): CategoryInterface
    {
        return $this->category;
    }
}