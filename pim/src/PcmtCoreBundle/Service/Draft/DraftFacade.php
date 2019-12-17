<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Draft;

use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Entity\AbstractDraft;
use PcmtCoreBundle\Entity\DraftInterface;
use PcmtCoreBundle\Entity\ProductDraftInterface;
use PcmtCoreBundle\Entity\ProductModelDraftInterface;

class DraftFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ProductDraftApprover */
    private $productDraftApprover;

    /** @var ProductModelDraftApprover */
    private $productModelDraftApprover;

    /** @var DraftSaverFactory */
    private $draftSaverFactory;

    public function __construct(
        ProductDraftApprover $productDraftApprover,
        ProductModelDraftApprover $productModelDraftApprover,
        EntityManagerInterface $entityManager,
        DraftSaverFactory $draftSaverFactory
    ) {
        $this->entityManager = $entityManager;
        $this->productDraftApprover = $productDraftApprover;
        $this->productModelDraftApprover = $productModelDraftApprover;
        $this->draftSaverFactory = $draftSaverFactory;
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

    public function updateDraft(DraftInterface $draft): void
    {
        $this->draftSaverFactory
            ->create($draft)
            ->save($draft);
    }
}
