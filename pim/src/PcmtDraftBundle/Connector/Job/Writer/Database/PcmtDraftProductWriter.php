<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductWriter;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\ProductNormalizer;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductDraft;

class PcmtDraftProductWriter extends ProductWriter
{
    /** @var UserInterface */
    private $user;

    /** @var ProductNormalizer */
    private $productNormalizer;

    /** @var SaverInterface */
    private $draftSaver;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function setProductNormalizer(ProductNormalizer $productNormalizer): void
    {
        $this->productNormalizer = $productNormalizer;
    }

    public function setProductDraftSaver(SaverInterface $productDraftSaver): void
    {
        $this->draftSaver = $productDraftSaver;
    }

    public function setProductSaver(SaverInterface $productSaver): void
    {
        $this->productSaver = $productSaver;
    }

    public function setProductBuilder(ProductBuilderInterface $productBuilder): void
    {
        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        foreach ($items as $item) {
            $product = null;
            if ($item->getId()) {
                $product = $item;
            } else {
                $product = $this->productBuilder->createProduct($item->getIdentifier(), $item->getFamily()->getCode());
                $this->productSaver->save($product);
            }
            if (null !== $product) {
                $data = $this->productNormalizer->normalize($item, 'standard');
                $draft = new ExistingProductDraft(
                    $product,
                    $data,
                    $this->user,
                    new \DateTime(),
                    AbstractDraft::STATUS_NEW
                );
                $this->draftSaver->save($draft);
            }
            $this->incrementCount($item);
        }
    }
}