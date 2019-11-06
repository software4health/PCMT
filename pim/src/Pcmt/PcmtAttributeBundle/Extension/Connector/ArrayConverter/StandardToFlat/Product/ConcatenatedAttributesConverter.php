<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;

class ConcatenatedAttributesConverter extends AbstractValueConverter implements ValueConverterInterface
{
    public function convert($attributeCode, $data)
    {
        return ['concatenated' => 'for now it\'s only crying' ];
    }
}