<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Remover;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use PcmtPermissionsBundle\Exception\NoCategoryAccessException;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class CategoryAwareRemover extends BaseRemover
{
    /** @var CategoryPermissionsCheckerInterface */
    private $permissionsChecker;

    public function setPermissionsChecker(
        CategoryPermissionsCheckerInterface $permissionsChecker
    ): void {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateObject($object): void
    {
        /** @var CategoryAwareInterface $object */
        parent::validateObject($object);

        if (!$this->permissionsChecker->hasAccessToProduct(
            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
            $object
        )) {
            throw new NoCategoryAccessException('User has not permissions to delete this product.');
        }
    }
}
