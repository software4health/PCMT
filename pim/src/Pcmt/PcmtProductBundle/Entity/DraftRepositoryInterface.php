<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

interface DraftRepositoryInterface
{
    public function findById(): ProductAbstractDraft;
}