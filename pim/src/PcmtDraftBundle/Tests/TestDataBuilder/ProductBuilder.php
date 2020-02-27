<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\Family;

class ProductBuilder
{
    /** @var ProductInterface */
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    public function withParent(ProductModel $parent): self
    {
        $this->product->setParent($parent);

        return $this;
    }

    public function withId(int $id): self
    {
        $this->product->setId($id);

        return $this;
    }

    public function withFamily(Family $family): self
    {
        $this->product->setFamily($family);

        return $this;
    }

    public function build(): Product
    {
        return $this->product;
    }
}