<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use InvalidArgumentException;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Saver\ProductDraftSaver;
use PcmtDraftBundle\Saver\ProductModelDraftSaver;

class DraftSaverFactory
{
    /** @var ProductDraftSaver */
    private $productDraftSaver;

    /** @var ProductModelDraftSaver */
    private $productModelDraftSaver;

    public function __construct(ProductDraftSaver $productDraftSaver, ProductModelDraftSaver $productModelDraftSaver)
    {
        $this->productDraftSaver = $productDraftSaver;
        $this->productModelDraftSaver = $productModelDraftSaver;
    }

    public function create(DraftInterface $draft): SaverInterface
    {
        switch (true) {
            case $draft instanceof ProductDraftInterface:
                return $this->productDraftSaver;
            case $draft instanceof ProductModelDraftInterface:
                return $this->productModelDraftSaver;
            default:
                throw new InvalidArgumentException('There is no saver for this draft type');
        }
    }
}