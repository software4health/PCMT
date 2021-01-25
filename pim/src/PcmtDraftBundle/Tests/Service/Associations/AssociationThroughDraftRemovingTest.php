<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Associations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Associations\AssociationThroughDraftRemoving;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\AssociationCollectionBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\AssociationTypeBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductAssociationBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelAssociationBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssociationThroughDraftRemovingTest extends TestCase
{
    /** @var SaverInterface|MockObject */
    private $productSaverMock;

    /** @var SaverInterface|MockObject */
    private $productModelSaverMock;

    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $objectFromDraftCreatorMock;

    /** @var DraftRepository|MockObject */
    private $draftRepositoryMock;

    protected function setUp(): void
    {
        $this->productSaverMock = $this->createMock(SaverInterface::class);
        $this->productModelSaverMock = $this->createMock(SaverInterface::class);
        $this->objectFromDraftCreatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->draftRepositoryMock = $this->createMock(DraftRepository::class);
    }

    public function dataRemoveProductExistingDraft(): array
    {
        $product1 = (new ProductBuilder())->build();
        $associationType = (new AssociationTypeBuilder())->build();

        $association = (new ProductAssociationBuilder())
            ->withType($associationType)
            ->withProduct($product1)
            ->build();

        $associations = (new AssociationCollectionBuilder())
            ->withAssociation($association)
            ->build();

        $productToBeChanged = (new ProductBuilder())
            ->withAssociations($associations)
            ->build();

        return [
            [$product1, $productToBeChanged, $associationType],
        ];
    }

    /**
     * @dataProvider dataRemoveProductExistingDraft
     */
    public function testRemoveProductExistingDraft(
        EntityWithAssociationsInterface $objectToBeRemovedFromAssociation,
        ProductInterface $objectToBeChanged,
        AssociationType $associationType
    ): void {
        $this->productSaverMock->expects($this->once())->method('save');

        $draft = (new ExistingProductDraftBuilder())->build();
        $this->draftRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($draft);
        $this->objectFromDraftCreatorMock->expects($this->once())->method('getObjectToCompare')->willReturn($objectToBeChanged);

        $service = $this->getRemovingService();
        $service->remove($objectToBeRemovedFromAssociation, $objectToBeChanged, $associationType);
    }

    public function dataRemoveProductModel(): array
    {
        $productModel1 = (new ProductModelBuilder())->build();
        $product1 = (new ProductBuilder())->build();
        $associationType = (new AssociationTypeBuilder())->build();

        $association1 = (new ProductAssociationBuilder())
            ->withType($associationType)
            ->withProduct($product1)
            ->build();

        $association2 = (new ProductModelAssociationBuilder())
            ->withType($associationType)
            ->withProductModel($productModel1)
            ->build();

        $associations = (new AssociationCollectionBuilder())
            ->withAssociation($association1)
            ->withAssociation($association2)
            ->build();

        $productModelToBeChanged = (new ProductModelBuilder())
            ->withAssociations($associations)
            ->build();

        return [
            [$product1, $productModelToBeChanged, $associationType],
            [$productModel1, $productModelToBeChanged, $associationType],
        ];
    }

    /**
     * @dataProvider dataRemoveProductModel
     */
    public function testRemoveProductModel(
        EntityWithAssociationsInterface $objectToBeRemovedFromAssociation,
        ProductModelInterface $objectToBeChanged,
        AssociationType $associationType
    ): void {
        $this->productModelSaverMock->expects($this->once())->method('save');

        $service = $this->getRemovingService();
        $service->remove($objectToBeRemovedFromAssociation, $objectToBeChanged, $associationType);
    }

    private function getRemovingService(): AssociationThroughDraftRemoving
    {
        return new AssociationThroughDraftRemoving(
            $this->productSaverMock,
            $this->productModelSaverMock,
            $this->objectFromDraftCreatorMock,
            $this->draftRepositoryMock
        );
    }
}