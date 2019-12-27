<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 *
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Entity\Mapping;

use PcmtCoreBundle\Entity\Attribute;

class E2OpenAttributeMapping
{
    /** @var int */
    private $id;

    /** @var Attribute */
    private $E2OpenAttribute;

    /** @var Attribute */
    private $mappedAttribute;

    public function getId()
    {
        return $this->id;
    }

    public function getE2OpenAttribute(): Attribute
    {
        return $this->E2OpenAttribute;
    }

    public function getMappedAttribute(): Attribute
    {
        return $this->mappedAttribute;
    }

}

