<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtSharedBundle\Service\Access;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class ProductAccessChecker implements ProductAccessCheckerInterface
{
    public function checkForUser(CategoryAwareInterface $product, UserInterface $user): bool
    {
        return true;
    }
}