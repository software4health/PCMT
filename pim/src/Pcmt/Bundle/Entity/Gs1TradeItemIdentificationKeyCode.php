<?php

declare(strict_types=1);

namespace Pcmt\Bundle\Entity;

use Akeneo\Pim\Structure\Component\AttributeTypes;

class Gs1TradeItemIdentificationKeyCode extends GS1Code
{
    public function getReferenceDataEntityType(): string
    {
        return AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT;
    }

    protected static function getClass(): string
    {
        return 'Gs1TradeItemIdentificationKeyCode';
    }
}