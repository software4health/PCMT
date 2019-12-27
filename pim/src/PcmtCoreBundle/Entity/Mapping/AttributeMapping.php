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

class AttributeMapping
{
    const MAPPING_TYPES = [
        'E2Open'
    ];
    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var Attribute */
    private $E2OpenAttribute;

    /** @var Attribute */
    private $mappedAttribute;

    private function __construct(string $type)
    {
        if(!in_array($type, self::MAPPING_TYPES)){
            throw new \InvalidArgumentException('Wrong mapping type.');
        }
        $this->type = $type;
    }

    public function create(string $type)
    {
        return new self($type);
    }

    public function getId(): ?int
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

    public function addMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): void
    {

    }
}

