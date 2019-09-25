<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pcmt\PcmtAttributeBundle\Entity\Attribute;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Model\Separator\SeparatorInterface;

class ConcatenatedAttribute extends Attribute //this class will be composite wrapper
{
    /** @var ArrayCollection $attributes */
    protected $attributes;

    /** @var string $separator */
    protected $separator;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    public function addAttribute(Attribute $attribute): void
    {
        $this->attributes->add($attribute);
    }

    public function setSeparator(SeparatorInterface $separator)
    {
        $this->separator = $separator->getSeparatorString();
    }

    public function getSeparator(): string
    {
        return $this->separator ?? SeparatorInterface::DEFAULT_SEPARATOR;
    }

    protected function serialize(): void
    {
        foreach ($this->attributes as $attribute);
    }
}
