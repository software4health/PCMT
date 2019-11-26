<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Service;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\AbstractDraft;
use Pcmt\PcmtProductBundle\Entity\DraftInterface;
use Pcmt\PcmtProductBundle\Exception\DraftViolationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DraftApprover
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ProductFromDraftCreator */
    protected $productFromDraftCreator;

    /** @var ProductSaver */
    private $productSaver;

    /** @var ValidatorInterface */
    private $productValidator;

    public function __construct(
        ProductFromDraftCreator $productFromDraftCreator,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ProductSaver $productSaver,
        ValidatorInterface $productValidator
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->productFromDraftCreator = $productFromDraftCreator;
        $this->productSaver = $productSaver;
        $this->productValidator = $productValidator;
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

    public function approve(DraftInterface $draft): void
    {
        $product = $this->productFromDraftCreator->getProductToSave($draft);

        $violations = $this->productValidator->validate($product);
        if (0 === $violations->count()) {
            $this->productSaver->save($product);
        } else {
            throw new DraftViolationException($violations, $product);
        }

        $this->updateDraftEntity($draft);
    }
}