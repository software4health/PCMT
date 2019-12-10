<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Entity\ReferenceData;

use Akeneo\Pim\Structure\Component\AttributeTypes;

class DataCarrierTypeCode extends \PcmtCoreBundle\Entity\ReferenceData\GS1Code
{
    public function getReferenceDataEntityType(): string
    {
        return AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT;
    }

    protected static function getClass(): string
    {
        return 'DataCarrierTypeCode';
    }
}