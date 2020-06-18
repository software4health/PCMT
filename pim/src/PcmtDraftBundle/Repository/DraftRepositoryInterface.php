<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

interface DraftRepositoryInterface extends ObjectRepository
{
    public function findWithStatus(int $statusId, int $offset, int $limit): array;

    public function countWithStatus(int $statusId): int;
}