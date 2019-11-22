<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ProductFetchEvent extends Event
{
    protected const NAME = 'product.fetched';

    /** @var int */
    protected $productId;

    public function __construct(string $productId)
    {
        $this->productId = (int) $productId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}