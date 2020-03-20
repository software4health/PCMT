<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Saver\DraftSaver;

class DraftFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var DraftApprover */
    private $productDraftApprover;

    /** @var DraftApprover */
    private $productModelDraftApprover;

    /** @var DraftSaver */
    private $draftSaver;

    public function __construct(
        DraftApprover $productDraftApprover,
        DraftApprover $productModelDraftApprover,
        EntityManagerInterface $entityManager,
        DraftSaver $draftSaver
    ) {
        $this->productDraftApprover = $productDraftApprover;
        $this->productModelDraftApprover = $productModelDraftApprover;
        $this->entityManager = $entityManager;
        $this->draftSaver = $draftSaver;
    }

    public function approveDraft(DraftInterface $draft): void
    {
        if ($draft instanceof ProductDraftInterface) {
            $this->productDraftApprover->approve($draft);
        } elseif ($draft instanceof ProductModelDraftInterface) {
            $this->productModelDraftApprover->approve($draft);
        } else {
            throw new \Exception('Unknown class: '.get_class($draft));
        }
    }

    public function rejectDraft(DraftInterface $draft): void
    {
        $draft->reject();
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }

    public function updateDraft(DraftInterface $draft): void
    {
        $this->draftSaver->save($draft);
    }
}
