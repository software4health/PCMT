<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class DraftRejecter
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GeneralObjectFromDraftCreator */
    private $creator;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        GeneralObjectFromDraftCreator $creator,
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->creator = $creator;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function reject(DraftInterface $draft): void
    {
        $objectToSave = $this->creator->getObjectToSave($draft);
        if (!$objectToSave) {
            throw new \Exception('pcmt.entity.draft.error.no_corresponding_object');
        }

        /** @var UserInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $violations = new ConstraintViolationList();

        if (!$this->categoryPermissionsChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::OWN_LEVEL, $objectToSave, $user)) {
            $violations->add(
                new ConstraintViolation(
                    'No permission to reject the draft: no "own" access to any of the categories of the product.',
                    '',
                    [],
                    '',
                    '',
                    ''
                )
            );
        }

        if (0 !== $violations->count()) {
            throw new DraftViolationException($violations, $objectToSave);
        }

        $draft->reject();

        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }
}