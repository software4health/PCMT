<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Widget;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PcmtCoreBundle\Entity\AbstractDraft;
use PcmtCoreBundle\Entity\ExistingProductDraft;
use PcmtCoreBundle\Entity\NewProductDraft;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DraftsFetcher
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var SecurityFacade */
    protected $securityFacade;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SecurityFacade $securityFacade
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->securityFacade = $securityFacade;
    }

    public function fetch(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $draftRepository = $this->entityManager->getRepository(AbstractDraft::class);
        $drafts = $draftRepository->getUserDrafts($user);

        $fetcherFormatted = [];
        foreach ($drafts as $draft) {
            switch (get_class($draft)) {
                case NewProductDraft::class:
                    $productLabel = $draft->getProductData()['identifier'] ?? '-no product-';

                    break;
                case ExistingProductDraft::class:
                    $productLabel = $draft->getProduct()->getIdentifier();

                    break;
            }

            $fetcherFormatted[$draft->getId()]['id'] = $draft->getId();
            $fetcherFormatted[$draft->getId()]['label'] = $productLabel;
            $createdAt = $draft->getCreatedAt();
            $createdAt->format('Y-m-d H:i');
            $fetcherFormatted[$draft->getId()]['createdAt'] = $draft->getCreatedAtFormatted();
            $fetcherFormatted[$draft->getId()]['author'] = $user->getFirstName() . ' ' . $user->getLastName();
        }

        return $fetcherFormatted;
    }
}