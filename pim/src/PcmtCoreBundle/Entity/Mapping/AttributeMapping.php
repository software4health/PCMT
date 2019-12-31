<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Entity\Mapping;

use PcmtCoreBundle\Entity\Attribute;

class AttributeMapping
{
    public const MAPPING_TYPES = [
        'E2Open',
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
    ) {
        if (!in_array($type, self::MAPPING_TYPES)) {
            throw new \InvalidArgumentException('Wrong mapping type.');
        }
        $this->mappingType = $type;
        $this->name = self::composeName($mappingAttribute, $mappedAttribute);
        $this->addMapping($mappingAttribute, $mappedAttribute);
    }

    public static function create(
        string $type,
        Attribute $mappingAttribute,
        Attribute $mappedAttribute
    ): self {
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

    public function getMappingType(): string
    {
        return $this->mappingType;
    }

    public function addMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): void
    {
        $this->mappingAttribute = $mappingAttribute;
        $this->mappedAttribute = $mappedAttribute;
    }

    public static function composeName(Attribute $mappingAttribute, Attribute $mappedAttribute): string
    {
        return $mappingAttribute->getCode() . '_' . $mappedAttribute->getCode();
    }
}
