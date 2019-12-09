<?php

declare(strict_types=1);

namespace PcmtProductBundle\Service\Draft;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use InvalidArgumentException;
use PcmtProductBundle\Entity\DraftInterface;
use PcmtProductBundle\Entity\ProductDraftInterface;
use PcmtProductBundle\Entity\ProductModelDraftInterface;
use PcmtProductBundle\Saver\ProductDraftSaver;
use PcmtProductBundle\Saver\ProductModelDraftSaver;

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