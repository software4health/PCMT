<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Extension\Factory;

class PcmtCommandFactory
{
    public function command(string $attributeClass): object
    {
        switch ($attributeClass) {
            default:
                throw new \InvalidArgumentException('Attribute type not recognized');
        }
    }
}