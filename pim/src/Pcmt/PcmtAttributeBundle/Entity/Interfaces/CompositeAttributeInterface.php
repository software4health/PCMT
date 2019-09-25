<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Entity\Interfaces;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\Common\Collections\ArrayCollection;

interface CompositeAttributeInterface
{
    public function getAttributes(): ArrayCollection;

    public function addAttribute(AttributeInterface $attribute): void;
}