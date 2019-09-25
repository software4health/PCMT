<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Model\Separator;

class CommaSeparator implements SeparatorInterface
{
    public function getSeparatorString(): string
    {
        return ',';
    }
}