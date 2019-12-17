<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Extension;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Extension\Factory\PcmtCommandFactory;

class PcmtAttributeManager
{
    /** @var mixed */
    private static $writeCommandInstance;

    public static function decorateAttributeInstance(
        string $attributeClass,
        AttributeInterface $attribute,
        string $field,
        array $value
    ): void {
        if (null === self::$writeCommandInstance) {
            $pcmtCommandFactory = new PcmtCommandFactory();
            self::$writeCommandInstance = $pcmtCommandFactory->command($attributeClass);
        }
        self::$writeCommandInstance->decorate($attribute, $field, $value);
    }
}