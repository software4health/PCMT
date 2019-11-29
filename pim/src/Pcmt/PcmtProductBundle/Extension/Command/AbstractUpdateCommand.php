<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Extension\Command;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

abstract class AbstractUpdateCommand
{
    /** @var AttributeInterface */
    protected $attribute;

    public function decorate(AttributeInterface $attribute, string $field, array $data): AttributeInterface
    {
        try {
            $this->validateAttribute($attribute);
            $this->validateDataFields($field);

            foreach ($data as $field => $value) {
                $this->updatePropertyValue($field, $value);
            }
        } catch (\InvalidArgumentException $argumentException) {
            return $attribute; // @todo -add logging if necessary
        }

        return $this->attribute;
    }

    protected function validateDataFields(string $field): void
    {
        if (!in_array($field, $this->getAvailableFields())) {
            throw new \InvalidArgumentException('Invalid attribute field name: ' . $field);
        }
    }

    abstract protected function validateAttribute(AttributeInterface $attribute): void;

    abstract protected function getAvailableFields(): array;

    abstract protected function updatePropertyValue(string $field, string $value): void;
}