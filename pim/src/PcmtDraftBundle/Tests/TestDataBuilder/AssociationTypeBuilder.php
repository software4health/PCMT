<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\AssociationType;

class AssociationTypeBuilder
{
    /** @var AssociationType */
    private $associationType;

    public function __construct()
    {
        $this->associationType = new AssociationType();
    }

    public function withId(int $id): self
    {
        $this->associationType->setId($id);

        return $this;
    }

    public function build(): AssociationType
    {
        return $this->associationType;
    }
}