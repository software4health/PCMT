<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

interface DraftInterface
{
    public const DRAFT_VERSION_NEW = 1;

    public function getId(): int;

    public function getType(): string;

    public function getAuthor(): UserInterface;
}