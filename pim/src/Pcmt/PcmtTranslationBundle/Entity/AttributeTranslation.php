<?php

namespace Pcmt\PcmtTranslationBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeTranslation as BaseAttributeTranslation;

class AttributeTranslation extends BaseAttributeTranslation
{
    protected $description;

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}