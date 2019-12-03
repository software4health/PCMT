<?php

declare(strict_types=1);

namespace PcmtProductBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

class NewProductDraft extends AbstractProductDraft
{
    public const TYPE = 'new product draft';

    public function __construct(
        array $productData,
        UserInterface $author,
        \DateTime $created,
        int $status
    ) {
        $this->productData = $productData;
        parent::__construct($author, $created, $status);
    }
}