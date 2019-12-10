<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

abstract class AbstractProductDraft extends AbstractDraft implements ProductDraftInterface
{
    protected function __construct(
        UserInterface $author,
        \DateTime $created,
        int $status
    ) {
        $this->author = $author;
        $this->created = $created;
        $this->status = $status;
        $this->version = self::DRAFT_VERSION_NEW;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }
}