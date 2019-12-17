<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Entity\DraftInterface;
use PcmtCoreBundle\Exception\DraftViolationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDraftApprover extends DraftApprover
{
    /** @var ProductFromDraftCreator */
    protected $creator;

    /** @var SaverInterface */
    private $saver;

    /** @var ValidatorInterface */
    private $validator;

    public function setCreator(ProductFromDraftCreator $creator): void
    {
        $this->creator = $creator;
    }

    public function setSaver(ProductSaver $saver): void
    {
        $this->saver = $saver;
    }

    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    public function approve(DraftInterface $draft): void
    {
        $product = $this->creator->getProductToSave($draft);

        $violations = $this->validator->validate($product);
        if (0 === $violations->count()) {
            $this->saver->save($product);
        } else {
            throw new DraftViolationException($violations, $product);
        }

        $this->updateDraftEntity($draft);
    }
}