<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ProductModelFetchEvent extends Event
{
    protected const NAME = 'product_model.fetched';

    /** @var int $productModelId */
    protected $productModelId;

    public function __construct($productModelId)
    {
        $this->productModelId = (int)$productModelId;
    }

    public function getProductModelId(): int
    {
        return $this->productModelId;
    }
}