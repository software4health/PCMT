<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class PendingProductDraft extends ProductAbstractDraft
{
    private const TYPE = ProductDraftInterface::TYPE_PENDING;

    public function __construct(
        ProductInterface $product,
        array $productData,
        UserInterface $author,
        \DateTime $created,
        int $version,
        int $status
    )
    {
        $this->product = $product;
        $this->productData = $productData;
        parent::__construct($author, $created, $version, $status);
    }

    public function nextVersion(): void
    {
        if(!$this->version) {
            $this->version = ProductDraftInterface::DRAFT_VERSION_NEW;
            return;
        }
        $this->version++;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}