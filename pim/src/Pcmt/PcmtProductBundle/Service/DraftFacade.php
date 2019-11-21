<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;

class DraftFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var DraftApprover */
    private $draftApprover;

    public function __construct(
        DraftApprover $draftApprover,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->draftApprover = $draftApprover;
    }

    public function approveDraft(ProductDraftInterface $draft): void
    {
        $this->draftApprover->approve($draft);
    }

    public function rejectDraft(ProductDraftInterface $draft): void
    {
        $draft->setStatus(AbstractProductDraft::STATUS_REJECTED);
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }
}
