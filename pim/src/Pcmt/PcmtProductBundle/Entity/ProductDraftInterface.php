<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

interface ProductDraftInterface
{
    public const STATUS_NEW = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_PENDING = 3;
    public const STATUS_REJECTED = 4;

    public const DRAFT_VERSION_NEW = 1;

    public const TYPE_NEW = 'new product draft';
    public const TYPE_PENDING = 'existing product draft';

    public function getId() : int ;
    public function getProductData() : ?array;
    public function getProduct(): ?ProductInterface;

}