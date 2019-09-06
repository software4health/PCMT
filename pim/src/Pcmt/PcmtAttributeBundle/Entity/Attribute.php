<?php

namespace Pcmt\PcmtAttributeBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\Attribute as BaseAttribute;
use Pcmt\PcmtTranslationBundle\Entity\AttributeTranslation;

class Attribute extends BaseAttribute
{
    public function getDescription()
    {
        $translated = ($this->getTranslation()) ? $this->getTranslation()->getDescription() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    public function setDescription($description)
    {
        $this->getTranslation()->setDescription($description);

        return $this;
    }

    public function getTranslationFQCN()
    {
        return AttributeTranslation::class;
    }
}