<?php

declare(strict_types=1);

namespace PcmtProductBundle\Entity;

interface DraftRepositoryInterface
{
    public function findById(): AbstractDraft;
}