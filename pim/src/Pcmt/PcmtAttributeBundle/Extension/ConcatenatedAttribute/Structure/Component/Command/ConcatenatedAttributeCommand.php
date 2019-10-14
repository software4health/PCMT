<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\Command;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Pcmt\PcmtAttributeBundle\Extension\Command\AbstractUpdateCommand;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class ConcatenatedAttributeCommand extends AbstractUpdateCommand
{
    private $concatenatedAttributes = [];

    protected function updatePropertyValue(string $field, $value): void
    {
        switch ($field) {
            case strpos($field, 'separator'):
                $this->attribute->setProperty('separators', $value); // @todo - add ability to serialize more than one separator in MVP++
                break;
            case strpos($field, 'attribute'):
                $this->updateConcatenatedAttributes($value);
                break;
        }
    }

    protected function getAvailableFields(): array
    {
        return ['concatenated'];
    }

    protected function validateAttribute(AttributeInterface $attribute): void
    {
        if($attribute->getId() && !$attribute->getType() === PcmtAtributeTypes::CONCATENATED_FIELDS){
            throw new \InvalidArgumentException('Attribute is of a wrong type. Attribute of type '
                . $attribute->getType() . ' passed, and attribute ' . PcmtAtributeTypes::CONCATENATED_FIELDS . ' expected.');
        }

        $this->attribute = $attribute;
    }

    protected function updateConcatenatedAttributes($value): void
    {
        $this->concatenatedAttributes[] = $value;
        $serializedAttributes = implode(',', $this->concatenatedAttributes);
        $this->attribute->setProperty('attributes', $serializedAttributes);
    }
}