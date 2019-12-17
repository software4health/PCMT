<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

abstract class AbstractProductModelDraft extends AbstractDraft implements ProductModelDraftInterface
{
    protected function __construct(
        UserInterface $author,
        \DateTime $created,
        int $status
    ) {
        $this->author = $author;
        $this->created = $created;
        $this->status = $status;
        $this->version = self::DRAFT_VERSION_NEW;
    }

    public function getProductModel(): ?ProductModelInterface
    {
        return $this->productModel;
    }
}