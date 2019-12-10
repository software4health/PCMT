<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\Attribute as BaseAttribute;

class Attribute extends BaseAttribute
{
    public function getDescription(): ?string
    {
        $translated = $this->getTranslation() ? $this->getTranslation()->getDescription() : null;

        return '' !== $translated && null !== $translated ? $translated : '['.$this->getCode().']';
    }

    public function setDescription(string $description): self
    {
        $this->getTranslation()->setDescription($description);

        return $this;
    }

    public function getTranslationFQCN(): string
    {
        return AttributeTranslation::class;
    }
}