<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\PcmtProductBundle\Service\DraftStatusListService;
use Pcmt\PcmtProductBundle\Service\DraftStatusTranslatorService;
use Pcmt\PcmtProductBundle\Entity\ProductAbstractDraft;
use Pcmt\PcmtProductBundle\Normalizer\DraftNormalizer;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @var DraftStatusTranslatorService
     */
    private $draftStatusTranslatorService;
    /**
     * @var DraftStatusListService
     */
    private $draftStatusListService;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        DraftNormalizer $draftNormalizer,
        DraftStatusTranslatorService $draftStatusTranslatorService,
        DraftStatusListService $draftStatusListService
    )
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->draftNormalizer = $draftNormalizer;
        $this->draftStatusTranslatorService = $draftStatusTranslatorService;
        $this->draftStatusListService = $draftStatusListService;
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
            "status" => $request->query->get('status') ?? ProductAbstractDraft::STATUS_NEW
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
        $ids = $this->draftStatusListService->getAll();
        foreach ($ids as $id) {
            $statuses[] = [
                'id' => $id,
                'name' => $this->draftStatusTranslatorService->getNameTranslated($id)
            ];
        }
        $data = [
            'statuses' => $statuses,
        ];

        return new JsonResponse($data);
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_reject")
     */
    public function rejectDraft(Request $request, ProductAbstractDraft $draft): JsonResponse
    {
        if (!$draft) {
            throw new NotFoundHttpException('The draft does not exist');
        }
        if ($draft->getStatus() !== ProductAbstractDraft::STATUS_NEW) {
            throw new BadRequestHttpException("You can only reject draft of status 'new'");
        }

        $draft->setStatus(ProductAbstractDraft::STATUS_REJECTED);
        $this->entityManager->persist($draft);
        $this->entityManager->flush();

        return new JsonResponse([]);
    }
}