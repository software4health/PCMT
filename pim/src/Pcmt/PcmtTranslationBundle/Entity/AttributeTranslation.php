<?php

declare(strict_types=1);

namespace Pcmt\PcmtTranslationBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeTranslation as BaseAttributeTranslation;

class AttributeTranslation extends BaseAttributeTranslation
{
    /** @var string */
    protected $description;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return AttributeTranslation
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }
}