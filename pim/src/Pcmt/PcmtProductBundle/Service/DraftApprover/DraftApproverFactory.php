<?php

namespace Pcmt\PcmtProductBundle\Service\DraftApprover;

use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;

class DraftApproverFactory
{
    /** @var NewProductDraftApprover */
    private $newProductDraftApprover;

    /** @var ExistingProductDraftApprover */
    private $existingProductDraftApprover;

    public function __construct(
        NewProductDraftApprover $newProductDraftApprover,
        ExistingProductDraftApprover $existingProductDraftApprover
    )
    {
        $this->newProductDraftApprover = $newProductDraftApprover;
        $this->existingProductDraftApprover = $existingProductDraftApprover;
    }

    public function getApproverForDraft(ProductDraftInterface $draft): DraftApproverInterface
    {
        $class = get_class($draft);
        switch ($class) {
            case NewProductDraft::class:
                return $this->newProductDraftApprover;
            case PendingProductDraft::class:
                return $this->existingProductDraftApprover;
            default:
                throw new NotImplementedException('No approver for class: '. $class);
        }
    }

}