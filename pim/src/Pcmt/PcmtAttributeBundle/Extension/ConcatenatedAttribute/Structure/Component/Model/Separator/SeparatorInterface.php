<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Model\Separator;

interface SeparatorInterface
{
    public const DEFAULT_SEPARATOR = ' : ';

    public function getSeparatorString(): string;
}