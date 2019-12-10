<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

final class ConcatenatedAttributeValue extends AbstractValue implements ConcatenatedAttributeValueInterface
{
    public function isEqual(ValueInterface $value): bool
    {
        return false;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return '';
    }
}