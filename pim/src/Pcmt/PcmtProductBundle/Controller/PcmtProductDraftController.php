<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;


use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Entity\ProductAbstractDraft;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PcmtProductDraftController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * approve existig draft
     * @AclAncestor("pcmt_permission_drafts_approve")
     */
    public function approveAction(Request $request): JsonResponse
    {
        throw new NotImplementedException('method not impemented');
    }

    public function getList(Request $request) : JsonResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $draftRepository = $this->entityManager->getRepository(ProductAbstractDraft::class);
        $drafts =  $draftRepository->getUserDrafts($user);

        $data = [];
        foreach ($drafts as $draft) {
            $data[$draft->getId()]['id'] = $draft->getId();
            $productLabel = 'no label';
            /** @var ProductAbstractDraft $draft */
            switch(get_class($draft)) {
                case NewProductDraft::class:
                    $productLabel = $draft->getProductData()['identifier'] ?? 'no label';
                    break;
                case PendingProductDraft::class:
                    $product = $draft->getProduct();
                    $productLabel = $product ? $product->getCode() : 'no product';
                    break;
            }
            $data[$draft->getId()]['label'] = $productLabel;
            $createdAt = $draft->getCreatedAt();
            $createdAt->format('Y-m-d H:i');
            $data[$draft->getId()]['createdAt'] = $draft->getCreatedAtFormatted();
            $author = $draft->getAuthor();
            $data[$draft->getId()]['author'] = $author ?
                $author->getFirstName() . ' ' . $author->getLastName() : 'no author';
        }

        return new JsonResponse(array_values($data));

    }
}