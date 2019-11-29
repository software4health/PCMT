<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Extension\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;

class ConcatenatedAttributesConverter extends AbstractValueConverter implements ValueConverterInterface
{
    protected const VALID_TYPES = [
        'attribute1' => ['string', 'array'],
        'separator'  => ['string'],
        'attribute2' => ['string', 'array'],
    ];

    /**
     * {@inheritdoc}
     */
    public function convert($attributeCode, $data): array
    {
        $convertedItem = [];
        $outputString = '';

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Serialized data should be array.');
        }
        foreach ($data as $attributeName => $value) {
            if (!array_key_exists($attributeName, self::VALID_TYPES) || !in_array(gettype($value), self::VALID_TYPES[$attributeName])) {
                throw new \InvalidArgumentException('Invalid type passed.');
            }
            if (is_array($value)) {
                foreach ($value as $item) {
                    $outputString .= $item;
                }
            } else {
                $outputString .= $value;
            }
        }

        $convertedItem[$attributeCode] = $outputString;

        return $convertedItem;
    }
}