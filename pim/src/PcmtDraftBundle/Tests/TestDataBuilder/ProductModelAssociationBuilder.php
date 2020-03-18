<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Structure\Component\Model\AssociationType;

class ProductModelAssociationBuilder
{
    /** @var ProductModelAssociation */
    private $association;

    public function __construct()
    {
        $this->association = new ProductModelAssociation();
    }

    public function withType(AssociationType $type): self
    {
        $this->association->setAssociationType($type);

        return $this;
    }

    public function build(): ProductModelAssociation
    {
        return $this->association;
    }
}