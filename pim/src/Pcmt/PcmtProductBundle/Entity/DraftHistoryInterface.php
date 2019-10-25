<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

interface DraftHistoryInterface
{
    public const PRODUCT_DRAFT_CREATED = 'Product Created as a Draft';
    public function setDraft(ProductDraftInterface $draft): void;
}