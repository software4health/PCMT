<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\NewObjectDraftInterface;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;

class GeneralObjectFromDraftCreator
{
    /** @var ObjectFromDraftCreatorInterface */
    private $productFromDraftCreator;

    /** @var ObjectFromDraftCreatorInterface */
    private $productModelFromDraftCreator;

    public function __construct(ObjectFromDraftCreatorInterface $productFromDraftCreator, ObjectFromDraftCreatorInterface $productModelFromDraftCreator)
    {
        $this->productFromDraftCreator = $productFromDraftCreator;
        $this->productModelFromDraftCreator = $productModelFromDraftCreator;
    }

    public function getObjectToSave(DraftInterface $draft): ?EntityWithAssociationsInterface
    {
        $creator = $this->getCreator($draft);
        if ($draft instanceof NewObjectDraftInterface) {
            return $creator->createNewObject($draft);
        }

        return $creator->createForSaveForDraftForExistingObject($draft);
    }

    public function getObjectToCompare(DraftInterface $draft): ?EntityWithAssociationsInterface
    {
        if ($draft instanceof NewObjectDraftInterface) {
            return $this->getCreator($draft)->createNewObject($draft);
        }

        return $this->createForComparingForDraftForExistingObject($draft);
    }

    private function getCreator(DraftInterface $draft): ObjectFromDraftCreatorInterface
    {
        if ($draft instanceof ProductDraftInterface) {
            return $this->productFromDraftCreator;
        } elseif ($draft instanceof ProductModelDraftInterface) {
            return $this->productModelFromDraftCreator;
        }
        throw new \Exception('Cannot find creator for class: ' . get_class($draft));
    }

    private function createForComparingForDraftForExistingObject(DraftInterface $draft): ?EntityWithAssociationsInterface
    {
        $object = $draft->getObject();
        if (!$object) {
            return null;
        }
        $newObject = clone $object;

        // cloning values, otherwise the original values would also be overwritten
        $newObject->setValues(new WriteValueCollection());
        $newObject->setAssociations(new ArrayCollection());
        $newObject->setCategories(new ArrayCollection());
        foreach ($object->getValuesForVariation() as $value) {
            $newObject->addValue($value);
        }
        $data = $draft->getProductData();
        if (isset($data['values'])) {
            $this->getCreator($draft)->updateObject($newObject, $data);
        }

        return $newObject;
    }
}