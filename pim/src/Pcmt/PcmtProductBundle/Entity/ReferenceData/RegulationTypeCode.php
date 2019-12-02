<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity\ReferenceData;

use Akeneo\Pim\Structure\Component\AttributeTypes;

class RegulationTypeCode extends GS1Code
{
    public function getReferenceDataEntityType(): string
    {
        return AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT;
    }

    protected static function getClass(): string
    {
        return 'RegulationTypeCode';
    }
}