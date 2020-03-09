<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use Symfony\Component\Security\Core\User\UserInterface;

class DraftBuilder
{
    /** @var int */
    private $id;

    /** @var ProductInterface */
    private $product;

    /** @var mixed[] */
    private $productData = [];

    /** @var UserInterface */
    private $author;

    /** @var \DateTime */
    private $created;

    /** @var int */
    private $status;

    public function __construct()
    {
        $this->id = 1;
        $this->product = (new ProductBuilder())->build();
        $this->productData = [];
        $this->author = (new UserBuilder())->build();
        $this->created = new \DateTime();
        $this->status = AbstractDraft::STATUS_NEW;
    }

    public function buildDraftOfANewProduct(): DraftInterface
    {
        $draft = new NewProductDraft(
            $this->productData,
            $this->author,
            $this->created,
            $this->status
        );

        $this->setDraftId($draft, $this->id);

        return $draft;
    }

    public function buildDraftOfAnExistingProduct(): DraftInterface
    {
        $draft = new ExistingProductDraft(
            $this->product,
            $this->productData,
            $this->author,
            $this->created,
            $this->status
        );

        $this->setDraftId($draft, $this->id);

        return $draft;
    }

    public function withId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    private function setDraftId(DraftInterface $draft, int $value): void
    {
        $reflection = new \ReflectionClass(get_class($draft));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($draft, $value);
    }
}