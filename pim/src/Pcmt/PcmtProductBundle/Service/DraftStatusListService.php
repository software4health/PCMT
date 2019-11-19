<?php

namespace Pcmt\PcmtProductBundle\Service;

use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;

class DraftStatusListService
{

    public function getAll(): array
    {
        return [
            AbstractProductDraft::STATUS_NEW,
            AbstractProductDraft::STATUS_APPROVED,
            AbstractProductDraft::STATUS_REJECTED,
        ];
    }

}