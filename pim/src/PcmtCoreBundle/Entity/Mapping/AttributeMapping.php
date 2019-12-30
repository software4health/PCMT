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
    private $mappingType;

    /** @var string|null */
    private $name;

    /** @var Attribute */
    private $mappingAttribute;

    /** @var Attribute */
    private $mappedAttribute;

    private function __construct(
        string $type,
        Attribute $mappingAttribute,
        Attribute $mappedAttribute
    )
    {
        if(!in_array($type, self::MAPPING_TYPES)){
            throw new \InvalidArgumentException('Wrong mapping type.');
        }
        $this->mappingType = $type;
        $this->addMapping($mappingAttribute, $mappedAttribute);
    }

    public static function create(
        string $type,
        Attribute $mappingAttribute,
        Attribute $mappedAttribute
    ): AttributeMapping
    {
        return new self($type, $mappingAttribute, $mappedAttribute);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMappingAttribute(): Attribute
    {
        return $this->mappingAttribute;
    }

    public function getMappedAttribute(): Attribute
    {
        return $this->mappedAttribute;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): void
    {
        $this->mappingAttribute = $mappingAttribute;
        $this->mappedAttribute = $mappedAttribute;
        $this->name = $mappingAttribute->getCode() . '_' . $mappedAttribute->getCode();
    }
}

