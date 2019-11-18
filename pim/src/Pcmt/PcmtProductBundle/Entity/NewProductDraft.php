<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

class NewProductDraft extends ProductAbstractDraft
{
    private const TYPE = ProductDraftInterface::TYPE_NEW;

    public function __construct
    (
        array $productData,
        UserInterface $author,
        \DateTime $created,
        int $version,
        int $status
    )
    {
        $this->productData = $productData;
        parent::__construct($author, $created, $version, $status);
    }

    public function nextVersion(): void
    {
        $this->version = ProductDraftInterface::DRAFT_VERSION_NEW;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}