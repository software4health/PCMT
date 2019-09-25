<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pcmt\PcmtAttributeBundle\Entity\Interfaces\CompositeAttributeInterface;

/**
 * class is a composite - will store references to attributes it contains.
 * By assigning it to the product it will either output attribute value for this product if it has this attrbute set or
 *
 */
class ConcatenatedAttribute extends Attribute implements CompositeAttributeInterface
{
    /** @var ArrayCollection $attributes */
    protected $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        parent::__construct();
    }

    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    public function addAttribute(AttributeInterface $attribute): void
    {
        if(!$this->attributes->contains($attribute)){
            $this->attributes->add($attribute);
        }
    }
}