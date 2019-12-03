<?php

declare(strict_types=1);

namespace PcmtProductBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

interface ProductModelDraftInterface extends DraftInterface
{
    public function getProductModel(): ?ProductModelInterface;
}