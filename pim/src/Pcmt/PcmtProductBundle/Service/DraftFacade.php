<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractDraft;
use Pcmt\PcmtProductBundle\Entity\DraftInterface;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Entity\ProductModelDraftInterface;

class DraftFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ProductDraftApprover */
    private $productDraftApprover;

    /** @var ProductModelDraftApprover */
    private $productModelDraftApprover;

    public function __construct(
        ProductDraftApprover $productDraftApprover,
        ProductModelDraftApprover $productModelDraftApprover,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->productDraftApprover = $productDraftApprover;
        $this->productModelDraftApprover = $productModelDraftApprover;
    }

    public function approveDraft(DraftInterface $draft): void
    {
        if ($draft instanceof ProductDraftInterface) {
            $this->productDraftApprover->approve($draft);
        } elseif ($draft instanceof ProductModelDraftInterface) {
            $this->productModelDraftApprover->approve($draft);
        } else {
            $class = get_class($draft);
            throw new \Exception('Unknown class: ' . $class);
        }
    }

    public function rejectDraft(DraftInterface $draft): void
    {
        $draft->setStatus(AbstractDraft::STATUS_REJECTED);
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }
}
