<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;

class ConcatenatedAttributesConverter extends AbstractValueConverter implements ValueConverterInterface
{
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

        foreach ($data as $element) {
            $value = $element['data'];
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