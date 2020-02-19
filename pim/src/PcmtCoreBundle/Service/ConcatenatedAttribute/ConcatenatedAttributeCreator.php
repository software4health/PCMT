<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\ConcatenatedAttribute;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Psr\Log\LoggerInterface;

class ConcatenatedAttributeCreator
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function update(AttributeInterface $attribute, string $field, array $data): AttributeInterface
    {
        try {
            $this->validateAttribute($attribute);
            $this->validateDataFields($field);

            foreach ($data as $field => $value) {
                $this->updatePropertyValue($attribute, $field, $value);
            }
        } catch (\InvalidArgumentException $argumentException) {
            $this->logger->error($argumentException->getMessage());
        }

        return $attribute;
    }

    private function validateDataFields(string $field): void
    {
        if (!in_array($field, $this->getAvailableFields())) {
            throw new \InvalidArgumentException('Invalid attribute field name: ' . $field);
        }
    }

    private function updatePropertyValue(AttributeInterface $attribute, string $field, string $value): void
    {
        switch ($field) {
            case mb_strpos($field, 'separator'):
                $attribute->setProperty(
                    'separators',
                    $value
                );
                break;
            case mb_strpos($field, 'attribute'):
                $this->updateConcatenatedAttributes($attribute, $value);

                break;
        }
    }

    private function validateAttribute(AttributeInterface $attribute): void
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
    }

    private function getAvailableFields(): array
    {
        return ['concatenated'];
    }

    private function updateConcatenatedAttributes(AttributeInterface $attribute, string $value): void
    {
        $concatenatedAttributes[] = $value;
        $serializedAttributes = implode(',', $concatenatedAttributes);
        $attribute->setProperty('attributes', $serializedAttributes);
    }
}