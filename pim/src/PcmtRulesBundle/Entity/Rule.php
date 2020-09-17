<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtRulesBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use DateTime;

class Rule
{
    /** @var int */
    private $id;

    /** @var string */
    private $uniqueId;

    /** @var FamilyInterface */
    private $sourceFamily;

    /** @var FamilyInterface */
    private $destinationFamily;

    /** @var DateTime */
    private $created;

    /** @var ?DateTime */
    private $updated;

    /** @var ?AttributeInterface */
    private $keyAttribute;

    public function __construct()
    {
    }

    public function setUniqueId(string $uniqueId): void
    {
        $this->uniqueId = $uniqueId;
    }

    public function setSourceFamily(?FamilyInterface $sourceFamily): void
    {
        $this->sourceFamily = $sourceFamily;
    }

    public function setDestinationFamily(?FamilyInterface $destinationFamily): void
    {
        $this->destinationFamily = $destinationFamily;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function setUpdated(?DateTime $updated): void
    {
        $this->updated = $updated;
    }

    public function setKeyAttribute(?AttributeInterface $keyAttribute): void
    {
        $this->keyAttribute = $keyAttribute;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getSourceFamily(): ?FamilyInterface
    {
        return $this->sourceFamily;
    }

    public function getDestinationFamily(): ?FamilyInterface
    {
        return $this->destinationFamily;
    }

    public function updateTimestamps(): void
    {
        $this->updated = new DateTime('now');
        if (null === $this->created) {
            $this->created = new DateTime('now');
        }
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getUpdated(): ?DateTime
    {
        return $this->updated;
    }

    public function getKeyAttribute(): ?AttributeInterface
    {
        return $this->keyAttribute;
    }
}
