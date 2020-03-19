<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Validator;

use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AttributeValidator
{
    /**
     * @param mixed|object $payload
     */
    public static function validateConcatenated(Attribute $object, ExecutionContextInterface $context, $payload): void
    {
        if (PcmtAtributeTypes::CONCATENATED_FIELDS !== $object->getType()) {
            return;
        }

        $properties = $object->getProperties();
        if (empty($properties) || empty($properties['attributes'])) {
            self::addTypeSpecificFieldsViolation($context);

            return;
        }

        $baseAttributes = explode(',', $properties['attributes']);
        if (2 !== count($baseAttributes)) {
            self::addTypeSpecificFieldsViolation($context);

            return;
        }
        foreach ($baseAttributes as $baseAttribute) {
            if (empty($baseAttribute)) {
                self::addTypeSpecificFieldsViolation($context);

                return;
            }
        }

        if (empty($properties['separators'])) {
            self::addTypeSpecificFieldsViolation($context);

            return;
        }
    }

    private static function addTypeSpecificFieldsViolation(ExecutionContextInterface $context): void
    {
        $context->buildViolation('pcmt.attribute.concatenated.type_specific_fields.error')
            ->addViolation();
    }
}