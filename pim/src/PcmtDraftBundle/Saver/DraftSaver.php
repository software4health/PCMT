<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingObjectDraftInterface;
use PcmtDraftBundle\Service\Draft\DraftExistenceChecker;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DraftSaver implements SaverInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var DraftExistenceChecker */
    private $draftExistenceChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        DraftExistenceChecker $draftExistenceChecker
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->draftExistenceChecker = $draftExistenceChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function save($draft, array $options = []): void
    {
        $this->validateDraft($draft);
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($draft, $options));
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($draft, $options));
    }

    protected function validateDraft(object $draft): void
    {
        if (!$draft instanceof DraftInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    DraftInterface::class,
                    get_class($draft)
                )
            );
        }

        if (!$draft->getId() && $draft instanceof ExistingObjectDraftInterface) {
            if ($this->draftExistenceChecker->checkIfDraftForObjectAlreadyExists($draft)) {
                throw new \InvalidArgumentException(
                    'There is already a draft for this object'
                );
            }
        }
    }
}