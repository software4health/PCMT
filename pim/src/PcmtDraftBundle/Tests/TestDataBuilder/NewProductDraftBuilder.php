<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\NewProductDraft;

class NewProductDraftBuilder extends AbstractDraftBuilder
{
    private const EXAMPLE_PRODUCT_DATA = [];
    private const EXAMPLE_DRAFT_ID = 1;

    /** @var NewProductDraft */
    private $newProductDraft;

    public function __construct()
    {
        $this->newProductDraft = new NewProductDraft(
            self::EXAMPLE_PRODUCT_DATA,
            (new UserBuilder())->build(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->setDraftId($this->newProductDraft, self::EXAMPLE_DRAFT_ID);
    }

    public function withId(int $id): self
    {
        $this->setDraftId($this->newProductDraft, $id);

        return $this;
    }

    public function build(): NewProductDraft
    {
        return $this->newProductDraft;
    }
}