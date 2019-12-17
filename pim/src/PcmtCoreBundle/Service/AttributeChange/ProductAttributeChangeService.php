<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\AttributeChange;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class ProductAttributeChangeService extends AttributeChangeService
{
    public function get(?ProductInterface $newProduct, ?ProductInterface $previousProduct): array
    {
        $this->changes = [];

        if (!$newProduct) {
            return $this->changes;
        }

        $newValues = $this->versioningSerializer->normalize($newProduct, 'flat');
        $previousValues = $previousProduct ?
            $this->versioningSerializer->normalize($previousProduct, 'flat') :
            [];

        foreach ($newValues as $attribute => $newValue) {
            $previousValue = $previousValues[$attribute] ?? null;
            $attribute = (string) $attribute;
            $this->createChange($attribute, $newValue, $previousValue);
        }

        return $this->changes;
    }
}