<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Service\AttributeChange;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

class ProductModelAttributeChangeService extends AttributeChangeService
{
    public function get(?ProductModelInterface $newProductModel, ?ProductModelInterface $previousProductModel): array
    {
        $this->changes = [];

        if (!$newProductModel) {
            return $this->changes;
        }

        $newValues = $this->versioningSerializer->normalize($newProductModel, 'flat');
        $previousValues = $previousProductModel ?
            $this->versioningSerializer->normalize($previousProductModel, 'flat') :
            [];

        foreach ($newValues as $attribute => $newValue) {
            $previousValue = $previousValues[$attribute] ?? null;
            $this->createChange($attribute, $newValue, $previousValue);
        }

        return $this->changes;
    }
}