<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Associations;

use PcmtDraftBundle\Service\Associations\AssociationThroughDraftAdding;
use PcmtDraftBundle\Service\Associations\BiDirectionalAssociationUpdater;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\AssociationCollectionBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductAssociationBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelAssociationBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BiDirectionalAssociationUpdaterTest extends TestCase
{
    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $objectFromDraftCreatorMock;

    /** @var AssociationThroughDraftAdding|MockObject */
    private $associationThroughDraftAddingMock;

    protected function setUp(): void
    {
        $this->objectFromDraftCreatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->associationThroughDraftAddingMock = $this->createMock(AssociationThroughDraftAdding::class);
    }

    public function testUpdate(): void
    {
        $connectedProduct = (new ProductBuilder())->build();
        $connectedProductModel = (new ProductModelBuilder())->build();
        $product = (new ProductBuilder())->withAssociations(
            (new AssociationCollectionBuilder())
                ->withAssociation((new ProductAssociationBuilder())->withProduct($connectedProduct)->build())
                ->withAssociation((new ProductAssociationBuilder())->withProduct($connectedProduct)->build())
                ->withAssociation((new ProductModelAssociationBuilder())->withProductModel($connectedProductModel)->build())
                ->build()
        )->build();
        $draft = (new ExistingProductDraftBuilder())->withProduct($product)->build();

        $this->objectFromDraftCreatorMock->method('getObjectToCompare')->willReturn($product);

        $this->associationThroughDraftAddingMock->expects($this->exactly(3))->method('add');

        $updater = $this->getUpdater();
        $updater->update($draft);
    }

    private function getUpdater(): BiDirectionalAssociationUpdater
    {
        return new BiDirectionalAssociationUpdater(
            $this->objectFromDraftCreatorMock,
            $this->associationThroughDraftAddingMock
        );
    }
}