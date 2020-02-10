<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Service\Draft\ObjectFromDraftCreatorInterface;
use PHPUnit\Framework\TestCase;

class GeneralObjectFromDraftCreatorTest extends TestCase
{
    /** @var ObjectFromDraftCreatorInterface */
    private $productCreator;

    /** @var ObjectFromDraftCreatorInterface */
    private $productModelCreator;

    protected function setUp(): void
    {
        $this->productCreator = $this->createMock(ObjectFromDraftCreatorInterface::class);
        $this->productModelCreator = $this->createMock(ObjectFromDraftCreatorInterface::class);
    }

    public function testGetObjectToSaveThrowsException(): void
    {
        $this->expectException(\Throwable::class);
        $service = new GeneralObjectFromDraftCreator($this->productCreator, $this->productModelCreator);
        $draft = $this->createMock(DraftInterface::class);
        $service->getObjectToSave($draft);
    }

    public function testGetObjectToSaveNewProductDraft(): void
    {
        $product = $this->createMock(EntityWithAssociationsInterface::class);
        $draft = $this->createMock(NewProductDraft::class);
        $this->productCreator->expects($this->once())->method('createNewObject')->willReturn($product);
        $service = new GeneralObjectFromDraftCreator($this->productCreator, $this->productModelCreator);
        $this->assertSame($product, $service->getObjectToSave($draft));
    }

    public function testGetObjectToSaveExistingProductDraft(): void
    {
        $productMock = $this->createMock(ProductModelInterface::class);
        $collection = new WriteValueCollection();
        $collection->add($this->createMock(ValueInterface::class));
        $productMock->method('getValuesForVariation')->willReturn($collection);
        $draft = $this->createMock(ExistingProductDraft::class);
        $this->productCreator->expects($this->once())->method('createForSaveForDraftForExistingObject')->willReturn($productMock);
        $service = new GeneralObjectFromDraftCreator($this->productCreator, $this->productModelCreator);
        $this->assertSame($productMock, $service->getObjectToSave($draft));
    }

    public function testGetObjectToCompareNewProductModelDraft(): void
    {
        $object = $this->createMock(EntityWithAssociationsInterface::class);
        $draft = $this->createMock(NewProductModelDraft::class);
        $this->productModelCreator->expects($this->once())->method('createNewObject')->willReturn($object);
        $service = new GeneralObjectFromDraftCreator($this->productCreator, $this->productModelCreator);
        $this->assertSame($object, $service->getObjectToCompare($draft));
    }

    public function testGetObjectToCompareExistingProductDraftNoProduct(): void
    {
        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getObject')->willReturn(null);
        $service = new GeneralObjectFromDraftCreator($this->productCreator, $this->productModelCreator);
        $this->productModelCreator->expects($this->never())->method('updateObject');
        $this->productCreator->expects($this->never())->method('updateObject');
        $this->assertNull($service->getObjectToCompare($draft));
    }

    public function testGetObjectToCompareExistingProductDraft(): void
    {
        $productMock = $this->createMock(ProductModelInterface::class);
        $collection = new WriteValueCollection();
        $collection->add($this->createMock(ValueInterface::class));
        $productMock->method('getValuesForVariation')->willReturn($collection);
        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getObject')->willReturn($productMock);
        $draft->method('getProductData')->willReturn([
            'values' => [
                'xxx' => 1,
            ],
        ]);
        $service = new GeneralObjectFromDraftCreator($this->productCreator, $this->productModelCreator);
        $this->productModelCreator->expects($this->once())->method('updateObject');
        $this->productCreator->expects($this->never())->method('updateObject');
        $this->assertInstanceOf(EntityWithAssociationsInterface::class, $service->getObjectToCompare($draft));
    }
}