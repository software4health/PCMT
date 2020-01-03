<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\AttributeChange;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class ProductAttributeChangeService extends AttributeChangeService
{
    public function get(?ProductInterface $newProduct, ?ProductInterface $previousProduct): array
    {
        return $this->getUniversal($newProduct, $previousProduct);
    }
}