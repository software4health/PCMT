<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;

class FamilyVariantBuilder
{
    /** @var FamilyVariant */
    private $familyVariant;

    public function __construct()
    {
        $this->familyVariant = new FamilyVariant();
    }

    public function withVariantAttributeSet(VariantAttributeSet $variantAttributeSet): self
    {
        $this->familyVariant->addVariantAttributeSet($variantAttributeSet);

        return $this;
    }

    public function build(): FamilyVariant
    {
        return $this->familyVariant;
    }
}