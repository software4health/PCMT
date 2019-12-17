<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Draft;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Entity\DraftInterface;
use PcmtCoreBundle\Exception\DraftViolationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductModelDraftApprover extends DraftApprover
{
    /** @var ProductModelFromDraftCreator */
    protected $creator;

    /** @var SaverInterface */
    private $saver;

    /** @var ValidatorInterface */
    private $validator;

    public function setCreator(ProductModelFromDraftCreator $creator): void
    {
        $this->creator = $creator;
    }

    public function setSaver(SaverInterface $saver): void
    {
        $this->saver = $saver;
    }

    public function setValidator(ValidatorInterface $productModelValidator): void
    {
        $this->validator = $productModelValidator;
    }

    public function approve(DraftInterface $draft): void
    {
        $productModel = $this->creator->getProductModelToSave($draft);

        $violations = $this->validator->validate($productModel);
        if (0 === $violations->count()) {
            $this->saver->save($productModel);
        } else {
            throw new DraftViolationException($violations, $productModel);
        }

        $this->updateDraftEntity($draft);
    }
}