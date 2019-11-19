<?php

namespace Pcmt\PcmtProductBundle\Service\DraftApprover;

use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;

class ExistingProductDraftApprover extends AbstractDraftApprover implements DraftApproverInterface
{
    public function approve(ProductDraftInterface $draft): void
    {
        throw new \Exception("Method not implemented.");
    }

}