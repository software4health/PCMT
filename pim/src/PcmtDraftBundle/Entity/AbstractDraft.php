<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Carbon\Carbon;

abstract class AbstractDraft implements DraftInterface
{
    public const STATUS_APPROVED = 2;
    public const STATUS_NEW = 1;
    public const STATUS_REJECTED = 4;

    // keep product or product-model related data here.
    // like family, groups etc. all the fields. - it helps rebuild product from new draft.
    /** @var mixed[] */
    protected $productData = [];

    /** @var int */
    protected $id = 0;

    /** @var \DateTime */
    protected $created;

    /** @var \DateTime */
    protected $updated;

    /** @var \DateTime */
    protected $approved;

    /** @var int */
    protected $version;

    /** @var int */
    protected $status;

    /** @var UserInterface */
    protected $author;

    /** @var UserInterface */
    protected $updatedBy;

    /** @var UserInterface */
    protected $approvedBy;

    /**
     * Has to be on this level, becase we keep product and product_model drafts in one table and ORM requires it.
     *
     * @var ?ProductModelInterface
     */
    protected $productModel;

    /**
     * Has to be on this level, becase we keep product and product_model drafts in one table and ORM requires it.
     *
     * @var ?ProductInterface
     */
    protected $product;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created;
    }

    public function getCreatedAtFormatted(): string
    {
        return Carbon::parse($this->created)->isoFormat('LLLL');
    }

    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $statusId): void
    {
        $this->status = $statusId;
    }

    public function setApproved(\DateTime $approved): void
    {
        $this->approved = $approved;
    }

    public function setApprovedBy(UserInterface $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
    }

    public function getType(): string
    {
        return static::TYPE;
    }

    public function getProductData(): ?array
    {
        return $this->productData;
    }

    public function setProductData(array $productData): void
    {
        $this->productData = $productData;
    }
}