<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractDraft;
use Pcmt\PcmtProductBundle\Entity\DraftInterface;

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

    public function approveDraft(DraftInterface $draft): void
    {
        $this->draftApprover->approve($draft);
    }

    public function rejectDraft(DraftInterface $draft): void
    {
        $draft->setStatus(AbstractDraft::STATUS_REJECTED);
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }
}
