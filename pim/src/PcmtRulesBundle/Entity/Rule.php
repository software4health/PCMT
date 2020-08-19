<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtRulesBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use DateTime;

class Rule
{
    /** @var string */
    private $uniqueId;

    /** @var Family */
    private $sourceFamily;

    /** @var Family */
    private $destinationFamily;

    /** @var DateTime */
    private $created;

    /** @var ?DateTime */
    private $updated;

    /** @var ?AttributeInterface */
    private $keyAttribute;

    public function __construct(
        string $uniqueId,
        Family $sourceFamily,
        Family $destinationFamily,
        DateTime $created,
        ?DateTime $updated,
        ?AttributeInterface $keyAttribute
    ) {
        $this->uniqueId = $uniqueId;
        $this->sourceFamily = $sourceFamily;
        $this->destinationFamily = $destinationFamily;
        $this->created = $created;
        $this->updated = $updated;
        $this->keyAttribute = $keyAttribute;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getSourceFamily(): Family
    {
        return $this->sourceFamily;
    }

    public function getDestinationFamily(): Family
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
