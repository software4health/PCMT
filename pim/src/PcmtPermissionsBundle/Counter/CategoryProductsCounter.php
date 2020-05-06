<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Counter;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryProductsCounter as BaseCategoryProductsCounter;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;

class CategoryProductsCounter extends BaseCategoryProductsCounter
{
    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        return parent::getItemsCountInCategory(
            CategoryWithAccess::class === get_class($category) ? $category->getCategory() : $category,
            $inChildren,
            $inProvided
        );
    }
}
