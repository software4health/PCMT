<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

interface DraftRepositoryInterface
{
    public function findById(): AbstractDraft;

    public function checkIfDraftForObjectAlreadyExists(DraftInterface $draft): bool;
}