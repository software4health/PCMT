<?php

declare(strict_types=1);

namespace PcmtProductBundle\Service\AttributeChange;

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
            $this->createChange($attribute, $newValue, $previousValue);
        }

        return $this->changes;
    }
}