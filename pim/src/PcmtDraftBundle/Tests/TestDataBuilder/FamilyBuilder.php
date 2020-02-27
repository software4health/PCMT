<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\Family;

class FamilyBuilder
{
    /** @var Family */
    private $family;

    public function __construct()
    {
        $this->family = new Family();
    }

    public function build(): Family
    {
        return $this->family;
    }
}