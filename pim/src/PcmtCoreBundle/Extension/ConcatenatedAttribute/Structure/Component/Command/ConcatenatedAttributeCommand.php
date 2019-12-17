<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\Command;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Extension\Command\AbstractUpdateCommand;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class ConcatenatedAttributeCommand extends AbstractUpdateCommand
{
    /** @var mixed[] */
    private $concatenatedAttributes = [];

    protected function updatePropertyValue(string $field, string $value): void
    {
        switch ($field) {
            case mb_strpos($field, 'separator'):
                $this->attribute->setProperty(
                    'separators',
                    $value
                ); // @todo - add ability to serialize more than one separator in MVP++
                break;
            case mb_strpos($field, 'attribute'):
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
        if ($attribute->getId() && PcmtAtributeTypes::CONCATENATED_FIELDS === !$attribute->getType()) {
            $message = sprintf(
                'Attribute is of a wrong type. Attribute of type ',
                $attribute->getType(),
                ' passed, and attribute ',
                PcmtAtributeTypes::CONCATENATED_FIELDS,
                ' expected.'
            );
            throw new \InvalidArgumentException($message);
        }

        $this->attribute = $attribute;
    }

    protected function updateConcatenatedAttributes(string $value): void
    {
        $this->concatenatedAttributes[] = $value;
        $serializedAttributes = implode(',', $this->concatenatedAttributes);
        $this->attribute->setProperty('attributes', $serializedAttributes);
    }
}