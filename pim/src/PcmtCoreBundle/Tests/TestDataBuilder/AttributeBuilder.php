<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\TestDataBuilder;

use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;

class AttributeBuilder
{
    /** @var Attribute */
    private $attribute;

    public function __construct()
    {
        $this->attribute = new Attribute();
    }

    public function withCode(string $code): self
    {
        $this->attribute->setCode($code);

        return $this;
    }

    public function withType(string $type): self
    {
        $this->attribute->setType($type);

        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->attribute->setProperties($properties);

        return $this;
    }

    public function build(): Attribute
    {
        return $this->attribute;
    }

    public function buildConcatenated(): Attribute
    {
        return $this->withType(PcmtAtributeTypes::CONCATENATED_FIELDS)->build();
    }
}