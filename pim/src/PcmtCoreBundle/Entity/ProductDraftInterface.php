<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

interface ProductDraftInterface extends DraftInterface
{
    public const TYPE_NEW = 'new product draft';
    public const TYPE_PENDING = 'existing product draft';

    public function getProductData(): ?array;

    public function getProduct(): ?ProductInterface;
}