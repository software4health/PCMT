<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DraftApprover
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GeneralObjectFromDraftCreator */
    private $creator;

    /** @var SaverInterface */
    private $saver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator,
        SaverInterface $saver,
        GeneralObjectFromDraftCreator $creator,
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->creator = $creator;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function approve(DraftInterface $draft): void
    {
        $objectToSave = $this->creator->getObjectToSave($draft);
        if (!$objectToSave) {
            throw new \Exception('pcmt.entity.draft.error.no_corresponding_object');
        }

        $violations = $this->validator->validate($objectToSave, null, ['Default', 'creation']);

        /** @var UserInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$this->categoryPermissionsChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::EDIT_LEVEL, $objectToSave, $user)) {
            $violations->add(
                new ConstraintViolation(
                    'No edit access to product basing on category.',
                    '',
                    [],
                    '',
                    '',
                    ''
                )
            );
        }

        if (0 === $violations->count()) {
            $this->saver->save($objectToSave);
        } else {
            throw new DraftViolationException($violations, $objectToSave);
        }

        $draft->approve($user);

        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }
}