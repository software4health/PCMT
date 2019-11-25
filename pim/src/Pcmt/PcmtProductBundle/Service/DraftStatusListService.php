<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Pcmt\PcmtProductBundle\Entity\AbstractDraft;

class DraftStatusListService
{
    public function getAll(): array
    {
        return [
            AbstractDraft::STATUS_NEW,
            AbstractDraft::STATUS_APPROVED,
            AbstractDraft::STATUS_REJECTED,
        ];
    }
}