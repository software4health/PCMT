<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\Value;

interface ConcatenatedAttributeValueInterface
{
    public function getData(): ?string;
}