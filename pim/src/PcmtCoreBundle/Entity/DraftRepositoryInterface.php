<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

interface DraftRepositoryInterface
{
    public function findById(): AbstractDraft;
}