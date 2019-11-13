<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Service\DraftStatusService;
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
    /**
     * @var DraftNormalizer
     */
    private $draftNormalizer;
    /**
     * @var DraftStatusService
     */
    private $draftStatusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        DraftNormalizer $draftNormalizer,
        DraftStatusService $draftStatusService
    )
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->draftNormalizer = $draftNormalizer;
        $this->draftStatusService = $draftStatusService;
    }

    /**
     * approve existig draft
     * @AclAncestor("pcmt_permission_drafts_approve")
     */
    public function approveAction(Request $request): JsonResponse
    {
        throw new NotImplementedException('method not impemented');
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_list")
     */
    public function getList(Request $request): JsonResponse
    {
        $criteria = [
            "status" => $request->query->get('status') ?? ProductDraftInterface::STATUS_NEW
        ];
        $draftRepository = $this->entityManager->getRepository(ProductAbstractDraft::class);

        $drafts = $draftRepository->findBy($criteria);

        $serializer = new Serializer([$this->draftNormalizer]);
        $data = $serializer->normalize($drafts);
        return new JsonResponse($data);
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_list")
     */
    public function getListParams(): JsonResponse
    {
        $statuses = [];
        $ids = $this->draftStatusService->getAll();
        foreach ($ids as $id) {
            $statuses[] = [
                'id' => $id,
                'name' => $this->draftStatusService->getNameTranslated($id)
            ];
        }
        $data = [
            'statuses' => $statuses,
        ];

        return new JsonResponse($data);
    }
}