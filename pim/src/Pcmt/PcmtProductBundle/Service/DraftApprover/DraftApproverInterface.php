<?php

namespace Pcmt\PcmtProductBundle\Service\DraftApprover;

use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;

interface DraftApproverInterface
{
    public function approve(ProductDraftInterface $draft): void;

}