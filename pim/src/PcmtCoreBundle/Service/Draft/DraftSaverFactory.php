<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Draft;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use InvalidArgumentException;
use PcmtCoreBundle\Entity\DraftInterface;
use PcmtCoreBundle\Entity\ProductDraftInterface;
use PcmtCoreBundle\Entity\ProductModelDraftInterface;
use PcmtCoreBundle\Saver\ProductDraftSaver;
use PcmtCoreBundle\Saver\ProductModelDraftSaver;

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