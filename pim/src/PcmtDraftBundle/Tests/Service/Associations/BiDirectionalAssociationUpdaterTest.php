<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Associations;

use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\Associations\AssociationThroughDraftAdding;
use PcmtDraftBundle\Service\Associations\BiDirectionalAssociationUpdater;
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

    public function dataUpdate(): array
    {
        $associationTypeCode = 'example_code';
        $productIdentifier2 = 'sdfdgfhdh';
        $productModelCode1 = 'sdfdsdsgdfhghg';

        $connectedProduct1 = (new ProductBuilder())->withIdentifier('aaaa')->build();
        $connectedProduct2 = (new ProductBuilder())->withIdentifier($productIdentifier2)->build();
        $connectedProductModel1 = (new ProductModelBuilder())->withCode($productModelCode1)->build();
        $connectedProductModel2 = (new ProductModelBuilder())->withCode('a')->build();

        $associationType = (new AssociationTypeBuilder())->withCode($associationTypeCode)->build();
        $association1 = (new ProductAssociationBuilder())
            ->withType($associationType)
            ->withProduct($connectedProduct1)
            ->withProduct($connectedProduct2)
            ->build();
        $association2 = (new ProductModelAssociationBuilder())
            ->withType($associationType)
            ->withProductModel($connectedProductModel1)
            ->withProductModel($connectedProductModel2)
            ->build();

        $product = (new ProductBuilder())->withAssociations(
            (new AssociationCollectionBuilder())
                ->withAssociation($association1)
                ->withAssociation($association2)
                ->build()
        )->build();
        $draft = (new ExistingProductDraftBuilder())->withProduct($product)->build();

        $associationData = [
            $associationTypeCode => [
                'products_bi_directional' => [
                    $productIdentifier2,
                ],
                'product_models_bi_directional' => [
                    $productModelCode1,
                ],
            ],
        ];

        return [
            [$draft, $associationData],
        ];
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(DraftInterface $draft, array $associationData): void
    {
        $this->objectFromDraftCreatorMock->method('getObjectToCompare')->willReturn($draft->getObject());

        $this->associationThroughDraftAddingMock->expects($this->exactly(2))->method('add');

        $updater = $this->getUpdater();
        $updater->update($draft, $associationData);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateEmptyParameter(DraftInterface $draft, array $associationData): void
    {
        $this->objectFromDraftCreatorMock->method('getObjectToCompare')->willReturn($draft->getObject());

        $this->associationThroughDraftAddingMock->expects($this->exactly(0))->method('add');

        $updater = $this->getUpdater();
        $updater->update($draft, []);
    }

    private function getUpdater(): BiDirectionalAssociationUpdater
    {
        return new BiDirectionalAssociationUpdater(
            $this->objectFromDraftCreatorMock,
            $this->associationThroughDraftAddingMock
        );
    }
}