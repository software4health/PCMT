<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\ProductAbstractDraft;
use Pcmt\PcmtProductBundle\Normalizer\DraftNormalizer;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
        $drafts =  $draftRepository->findAll();

        $normalizer = new DraftNormalizer();
        $normalizers = [$normalizer];

        $serializer = new Serializer($normalizers);
        $data = $serializer->normalize($drafts);
        return new JsonResponse($data);
    }
}