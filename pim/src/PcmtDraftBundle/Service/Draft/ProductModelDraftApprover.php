<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
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
        if (!$productModel) {
            $violation = new ConstraintViolation(
                'No corresponding product model found.',
                'No corresponding product model found.',
                [],
                $draft,
                'productModel',
                'no'
            );

            $violations = new ConstraintViolationList();
            $violations->add($violation);
        } else {
            $violations = $this->validator->validate($productModel);
        }

        if (0 === $violations->count()) {
            $this->saver->save($productModel);
        } else {
            throw new DraftViolationException($violations, $productModel);
        }

        $this->updateDraftEntity($draft);
    }
}