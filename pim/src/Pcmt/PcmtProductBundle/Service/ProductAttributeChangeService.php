<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pcmt\PcmtProductBundle\Entity\AttributeChange;

class ProductAttributeChangeService
{
    /** @var AttributeChange[] */
    private $changes = [];

    public function get(?ProductInterface $newProduct, ?ProductInterface $previousProduct): array
    {
        $this->changes = [];

        if (!$newProduct) {
            return $this->changes;
        }

        $newValues = $newProduct->getValues();
        $previousValues = $previousProduct ? $previousProduct->getValues() : null;

        $this->createChange(
            'Family',
            $newProduct && $newProduct->getFamily() ?
                $newProduct->getFamily()->getCode() : null,
            $previousProduct && $previousProduct->getFamily() ?
                $previousProduct->getFamily()->getCode() : null
        );

        $this->createChange(
            'Identifier',
            $newProduct ? $newProduct->getIdentifier() : null,
            $previousProduct ? $previousProduct->getIdentifier() : null
        );

        foreach ($newValues as $newValue) {
            /** @var ValueInterface $newValue */
            $previousValue = $previousValues ? $previousValues->getByCodes($newValue->getAttributeCode()) : null;
            $this->createChange(
                $newValue->getAttributeCode(),
                $newValue->getData(),
                $previousValue ? $previousValue->getData() : null
            );
        }

        return $this->changes;
    }

    /**
     * @param string $attribute
     */
    private function createChange($attribute, $value, $previousValue): void
    {
        if ($value === $previousValue) {
            return;
        }
        $this->changes[] = new AttributeChange(
            $attribute,
            is_array($previousValue) ? json_encode($previousValue) : (string) $previousValue,
            is_array($value) ? json_encode($value) : (string) $value
        );
    }
}