<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Structure\Component\Model\AssociationType;

class ProductAssociationBuilder
{
    /** @var ProductAssociation */
    private $association;

    public function __construct()
    {
        $this->association = new ProductAssociation();
    }

    public function withType(AssociationType $type): self
    {
        $this->association->setAssociationType($type);

        return $this;
    }

    public function build(): ProductAssociation
    {
        return $this->association;
    }
}