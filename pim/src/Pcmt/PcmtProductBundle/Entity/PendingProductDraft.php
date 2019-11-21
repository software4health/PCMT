<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class PendingProductDraft extends AbstractProductDraft
{
    private const TYPE = ProductDraftInterface::TYPE_PENDING;

    public function __construct(
        ProductInterface $product,
        array $productData,
        UserInterface $author,
        \DateTime $created,
        int $status
    ) {
        $this->product = $product;
        $this->productData = $productData;
        parent::__construct($author, $created, $status);
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}