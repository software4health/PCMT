<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Extension\Factory;

/* @todo analyse if this class is still used and needed */
use Pcmt\PcmtAttributeBundle\Entity\ConcatenatedAttribute;
use Pcmt\PcmtProductBundle\Extension\ConcatenatedAttribute\Structure\Component\Command\ConcatenatedAttributeCommand;

class PcmtCommandFactory
{
    public function command(string $attributeClass): object
    {
        switch ($attributeClass) {
            case ConcatenatedAttribute::class:
                return new ConcatenatedAttributeCommand();
            default:
                throw new \InvalidArgumentException('Attribute type not recognized');
        }
    }
}