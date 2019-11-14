<?php

namespace Pcmt\PcmtProductBundle\Service;

use Pcmt\PcmtProductBundle\Entity\ProductAbstractDraft;

class DraftStatusListService
{

    public function getAll(): array
    {
        return [
            ProductAbstractDraft::STATUS_NEW,
            ProductAbstractDraft::STATUS_APPROVED,
            ProductAbstractDraft::STATUS_REJECTED,
        ];
    }

}