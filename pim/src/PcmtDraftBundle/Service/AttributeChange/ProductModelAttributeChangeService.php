<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\AttributeChange;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

class ProductModelAttributeChangeService extends AttributeChangeService
{
    public function get(?ProductModelInterface $newProductModel, ?ProductModelInterface $previousProductModel): array
    {
        return $this->getUniversal($newProductModel, $previousProductModel);
    }
}