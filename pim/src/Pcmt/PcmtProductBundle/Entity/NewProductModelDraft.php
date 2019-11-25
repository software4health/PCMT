<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

class NewProductModelDraft extends AbstractProductModelDraft
{
    public const TYPE = 'new product model draft';

    public function __construct(
        array $productModelData,
        UserInterface $author,
        \DateTime $created,
        int $status
    ) {
        $this->productData = $productModelData;
        parent::__construct($author, $created, $status);
    }
}