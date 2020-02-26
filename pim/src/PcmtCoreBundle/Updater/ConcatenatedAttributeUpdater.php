<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Psr\Log\LoggerInterface;

class ConcatenatedAttributeUpdater
{
    /** @var LoggerInterface */
    private $logger;

    /** @var string[] */
    private $concatenatedAttributes = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->concatenatedAttributes = [];
    }

    public function update(AttributeInterface $attribute, array $data): AttributeInterface
    {
        try {
            $this->validateAttribute($attribute);
            foreach ($data as $field => $value) {
                $this->updatePropertyValue($attribute, $field, $value);
            }
        } catch (\InvalidArgumentException $argumentException) {
            $this->logger->error($argumentException->getMessage());
        }

        return $attribute;
    }

    private function updatePropertyValue(AttributeInterface $attribute, string $field, string $value): void
    {
        switch (true) {
            case 0 === mb_strpos($field, 'separator'):
                $attribute->setProperty('separators', $value);
                break;
            case 0 === mb_strpos($field, 'attribute'):
                $this->updateConcatenatedAttributes($attribute, $value);
                break;
        }
    }

    private function validateAttribute(AttributeInterface $attribute): void
    {
        if (PcmtAtributeTypes::CONCATENATED_FIELDS !== $attribute->getType()) {
            $message = sprintf(
                'Attribute is of a wrong type. Attribute of type %s passed, and attribute %s expected.',
                $attribute->getType(),
                PcmtAtributeTypes::CONCATENATED_FIELDS
            );

            throw new \InvalidArgumentException($message);
        }
    }

    private function updateConcatenatedAttributes(AttributeInterface $attribute, string $value): void
    {
        $this->concatenatedAttributes[] = $value;
        $serializedAttributes = implode(',', $this->concatenatedAttributes);
        $attribute->setProperty('attributes', $serializedAttributes);
    }
}