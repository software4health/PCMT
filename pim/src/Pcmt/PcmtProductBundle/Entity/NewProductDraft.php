<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

class NewProductDraft extends AbstractProductDraft
{
    private const TYPE = ProductDraftInterface::TYPE_NEW;

    public function __construct
    (
        array $productData,
        UserInterface $author,
        \DateTime $created,
        int $status
    )
    {
        $this->productData = $productData;
        parent::__construct($author, $created, $status);
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}