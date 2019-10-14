<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\Factory;

use Pcmt\PcmtAttributeBundle\Entity\ConcatenatedAttribute;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Command\ConcatenatedAttributeCommand;

class PcmtCommandFactory
{
    public function command(string $attributeClass)
    {
        switch ($attributeClass){
            case ConcatenatedAttribute::class:
                return new ConcatenatedAttributeCommand();
            default:
                throw new \InvalidArgumentException('Attribute type not recognized');
        }
    }
}