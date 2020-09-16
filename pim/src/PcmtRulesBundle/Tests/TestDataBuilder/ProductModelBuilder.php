<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Doctrine\Common\Collections\Collection;

class ProductModelBuilder
{
    public const EXAMPLE_CODE = 'a124';

    /** @var ProductModelInterface */
    private $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productModel->setCode(self::EXAMPLE_CODE);
    }

    public function addValue(ValueInterface $value): self
    {
        $this->productModel->getValues()->add($value);

        return $this;
    }

    public function withAssociations(Collection $collection): self
    {
        $this->productModel->setAssociations($collection);

        return $this;
    }

    public function build(): ProductModelInterface
    {
        return $this->productModel;
    }
}