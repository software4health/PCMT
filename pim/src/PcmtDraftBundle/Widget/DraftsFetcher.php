<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Widget;

use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftsFetcher
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var NormalizerInterface */
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        NormalizerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function fetch(): array
    {
        $criteria = [
            'status' => AbstractDraft::STATUS_NEW,
        ];
        $draftRepository = $this->entityManager->getRepository(AbstractDraft::class);
        $drafts = $draftRepository->findBy($criteria, null, 20, 0);

        return $this->serializer->normalize($drafts);
    }
}