<?php

declare(strict_types=1);

namespace PcmtProductBundle\Service;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use PcmtProductBundle\Entity\AbstractDraft;
use PcmtProductBundle\Entity\DraftInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class DraftApprover
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    protected function updateDraftEntity(DraftInterface $draft): void
    {
        $draft->setStatus(AbstractDraft::STATUS_APPROVED);
        $draft->setApproved(Carbon::now());
        $user = $this->tokenStorage->getToken()->getUser();
        /** @var UserInterface $user */
        $draft->setApprovedBy($user);
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
    }

    abstract public function approve(DraftInterface $draft): void;
}