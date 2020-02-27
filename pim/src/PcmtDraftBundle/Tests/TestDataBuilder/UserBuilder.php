<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\User;

class UserBuilder
{
    /** @var User */
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function build(): User
    {
        return $this->user;
    }
}