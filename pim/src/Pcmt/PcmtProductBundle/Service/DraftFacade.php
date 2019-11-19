<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\DraftApprover\DraftApproverFactory;

class DraftFacade
{
    /**
     * @var DraftApproverFactory
     */
    private $draftApproverFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        DraftApproverFactory $draftApproverFactory,
        EntityManagerInterface $entityManager
    )
    {
        $this->draftApproverFactory = $draftApproverFactory;
        $this->entityManager = $entityManager;
    }

    public function approveDraft(ProductDraftInterface $draft): void
    {
        $this->draftApproverFactory->getApproverForDraft($draft)->approve($draft);
    }

    public function rejectDraft(ProductDraftInterface $draft): void
    {
        $draft->setStatus(AbstractProductDraft::STATUS_REJECTED);
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }
}
