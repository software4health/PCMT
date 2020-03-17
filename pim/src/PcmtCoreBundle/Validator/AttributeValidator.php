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
        if ($properties) {
            if (!empty($properties['attributes'])) {
                if (2 === count(explode(',', $properties['attributes']))) {
                    if (!empty($properties['separators'])) {
                        // additional properties filled.
                        return;
                    }
                }
            }
        }

        $context->buildViolation('pcmt.attribute.concatenated.type_specific_fields.error')
            ->addViolation();
    }
}