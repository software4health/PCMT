<?php

declare(strict_types=1);

namespace PcmtProductBundle\Extension\ConcatenatedAttribute\Structure\Component\Value;

interface ConcatenatedAttributeValueInterface
{
    public function getData(): ?string;
}