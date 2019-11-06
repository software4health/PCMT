<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;

class ConcatenatedAttributesConverter extends AbstractValueConverter implements ValueConverterInterface
{
    public function convert($attributeCode, $data): array
    {
        $convertedItem = [];
        $testoutputstring = '';

        foreach ($data as $value){
            if(is_array($value)){
                foreach ($value as $key => $item){
                    $testoutputstring .= $item;
                }
            } else {
                $testoutputstring .= $value;
            }
        }

        $convertedItem[$attributeCode] = $testoutputstring;
        return $convertedItem;
    }
}