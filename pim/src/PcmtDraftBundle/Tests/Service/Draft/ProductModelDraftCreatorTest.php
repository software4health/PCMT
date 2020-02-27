<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Service\Draft\ProductModelDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProductModelDraftCreatorTest extends TestCase
{
    /** @var ProductModelDraftCreator */
    private $productModelDraftCreator;

    protected function setUp(): void
    {
        $this->productModelDraftCreator = new ProductModelDraftCreator();
    }

    public function testCreateWhenProductModelExists(): void
    {
        $productModel = (new ProductModelBuilder())
            ->withId(143)
            ->build();

        $user = (new UserBuilder())
            ->build();

        $draft = $this->productModelDraftCreator->create(
            $productModel,
            ['ATTRIBUTE' => 'VALUE'],
            $user,
            AbstractDraft::STATUS_NEW
        );

        $this->assertInstanceOf(ExistingProductModelDraft::class, $draft);
        $this->assertEquals(['ATTRIBUTE' => 'VALUE'], $draft->getProductData());
    }

    public function testCreateWhenProductModelDoesNotExist(): void
    {
        $productModel = (new ProductModelBuilder())
            ->build();

        $user = (new UserBuilder())
            ->build();

        $draft = $this->productModelDraftCreator->create(
            $productModel,
            ['ATTRIBUTE' => 'VALUE'],
            $user,
            AbstractDraft::STATUS_NEW
        );

        $this->assertInstanceOf(NewProductModelDraft::class, $draft);
        $this->assertEquals(['ATTRIBUTE' => 'VALUE'], $draft->getProductData());
    }
}