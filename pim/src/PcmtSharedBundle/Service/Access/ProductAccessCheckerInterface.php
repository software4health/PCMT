<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Service\Access;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

interface ProductAccessCheckerInterface
{
    public function checkForUser(CategoryAwareInterface $product, UserInterface $user): bool;
}