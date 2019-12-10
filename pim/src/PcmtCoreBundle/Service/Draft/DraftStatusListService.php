<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Draft;

use PcmtCoreBundle\Entity\AbstractDraft;

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