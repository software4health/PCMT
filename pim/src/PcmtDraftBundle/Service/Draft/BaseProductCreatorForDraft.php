<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;

class BaseProductCreatorForDraft implements BaseEntityCreatorInterface
{
    /** @var ProductBuilderInterface */
    private $productBuilder;

    public function __construct(ProductBuilderInterface $productBuilder)
    {
        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function create(EntityWithFamilyVariantInterface $product)
    {
        return $this->productBuilder->createProduct($product->getIdentifier(), $product->getFamily()->getCode());
    }
}