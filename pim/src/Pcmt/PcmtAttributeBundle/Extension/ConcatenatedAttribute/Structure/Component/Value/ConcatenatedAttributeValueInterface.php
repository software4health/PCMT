<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Value;

interface ConcatenatedAttributeValueInterface
{
    public function getData(): ?string;
}