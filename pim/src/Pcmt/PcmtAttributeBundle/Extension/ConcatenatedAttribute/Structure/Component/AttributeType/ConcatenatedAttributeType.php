<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypeInterface;

class ConcatenatedAttributeType implements AttributeTypeInterface
{
    protected $backendType = PcmtAtributeTypes::BACKEND_TYPE_CONCATENATED;

    public function getName()
    {
        return PcmtAtributeTypes::CONCATENATED_FIELDS;
    }

    public function getBackendType()
    {
        return $this->backendType;
    }

    public function isUnique()
    {
        return false;
    }
}