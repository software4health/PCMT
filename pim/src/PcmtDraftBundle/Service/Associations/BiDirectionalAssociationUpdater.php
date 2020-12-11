<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Associations;

use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;

class BiDirectionalAssociationUpdater
{
    /** @var GeneralObjectFromDraftCreator */
    private $generalObjectFromDraftCreator;

    /** @var AssociationThroughDraftAdding */
    private $associationThroughDraftAdding;

    public function __construct(
        GeneralObjectFromDraftCreator $generalObjectFromDraftCreator,
        AssociationThroughDraftAdding $associationThroughDraftAdding
    ) {
        $this->generalObjectFromDraftCreator = $generalObjectFromDraftCreator;
        $this->associationThroughDraftAdding = $associationThroughDraftAdding;
    }

    public function update(DraftInterface $draft): void
    {
        $object = $this->generalObjectFromDraftCreator->getObjectToCompare($draft);
        $associations = $object->getAllAssociations();
        foreach ($associations as $association) {
            $products = $association->getProducts();
            foreach ($products as $product) {
                $this->associationThroughDraftAdding->add($object, $product, $association->getAssociationType());
            }

            $productModels = $association->getProductModels();
            foreach ($productModels as $productModel) {
                $this->associationThroughDraftAdding->add($object, $productModel, $association->getAssociationType());
            }
        }
    }
}