<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypeInterface;

class ConcatenatedAttributeType implements AttributeTypeInterface
{
    /** @var string */
    protected $backendType = PcmtAtributeTypes::BACKEND_TYPE_CONCATENATED;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return PcmtAtributeTypes::CONCATENATED_FIELDS;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique()
    {
        return false;
    }
}