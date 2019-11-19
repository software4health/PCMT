<?php

namespace Pcmt\PcmtProductBundle\Service\DraftApprover;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\NewProductFromDraftCreator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NewProductDraftApprover extends AbstractDraftApprover implements DraftApproverInterface
{
    /** @var NewProductFromDraftCreator */
    private $newProductFromDraftCreator;

    public function __construct(
        NewProductFromDraftCreator $newProductFromDraftCreator,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->newProductFromDraftCreator = $newProductFromDraftCreator;
        parent::__construct($entityManager, $tokenStorage);
    }

    public function approve(ProductDraftInterface $draft): void
    {
        $this->newProductFromDraftCreator->create($draft);
        $this->updateDraftEntity($draft);
    }
}