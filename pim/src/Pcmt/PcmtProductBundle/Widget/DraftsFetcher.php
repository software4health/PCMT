<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Widget;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DraftsFetcher
{
    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var TokenStorageInterface $tokenStorage */
    protected $tokenStorage;

    /** @var SecurityFacade $securityFacade */
    protected $securityFacade;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SecurityFacade $securityFacade
    )
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->securityFacade = $securityFacade;
    }

    public function fetch(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $draftRepository = $this->entityManager->getRepository(AbstractProductDraft::class);
        $drafts =  $draftRepository->getUserDrafts($user);

        $fetcherFormatted = [];
        foreach ($drafts as $draft){

            switch(get_class($draft)){
                case NewProductDraft::class:

                    $productLabel = $draft->getProductData()['identifier'] ?? '-no product-';
                    break;
                case PendingProductDraft::class:
                    $productLabel = $draft->getProduct()->getIdentifier();
                    break;
            }

            $fetcherFormatted[$draft->getId()]['id'] = $draft->getId();
            $fetcherFormatted[$draft->getId()]['label'] = $productLabel;
            $createdAt = $draft->getCreatedAt();
            $createdAt->format('Y-m-d H:i');
            $fetcherFormatted[$draft->getId()]['createdAt'] = $draft->getCreatedAtFormatted();
            $fetcherFormatted[$draft->getId()]['author'] =  $user->getFirstName() . ' ' . $user->getLastName();
        }

        return $fetcherFormatted;
    }
}