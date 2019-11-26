<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pcmt\PcmtProductBundle\Entity\AttributeChange;

class ProductModelAttributeChangeService
{
    /** @var AttributeChange[] */
    private $changes = [];

    public function get(?ProductModelInterface $newProductModel, ?ProductModelInterface $previousProductModel): array
    {
        $this->changes = [];

        if (!$newProductModel) {
            return $this->changes;
        }

        $newValues = $newProductModel->getValues();
        $previousValues = $previousProductModel ? $previousProductModel->getValues() : null;

        $this->createChange(
            'Family Variant',
            $newProductModel && $newProductModel->getFamilyVariant() ?
                $newProductModel->getFamilyVariant()->getCode() : null,
            $previousProductModel && $previousProductModel->getFamilyVariant() ?
                $previousProductModel->getFamilyVariant()->getCode() : null
        );

        $this->createChange(
            'Code',
            $newProductModel ? $newProductModel->getCode() : null,
            $previousProductModel ? $previousProductModel->getCode() : null
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