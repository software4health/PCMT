<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pcmt\PcmtProductBundle\Entity\AbstractDraft;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Exception\DraftViolationException;
use Pcmt\PcmtProductBundle\Normalizer\ProductDraftNormalizer;
use Pcmt\PcmtProductBundle\Normalizer\ProductModelDraftNormalizer;
use Pcmt\PcmtProductBundle\Service\DraftFacade;
use Pcmt\PcmtProductBundle\Service\DraftStatusListService;
use Pcmt\PcmtProductBundle\Service\DraftStatusTranslatorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class PcmtProductDraftController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ProductDraftNormalizer */
    private $productDraftNormalizer;

    /** @var DraftStatusTranslatorService */
    private $draftStatusTranslatorService;

    /** @var DraftStatusListService */
    private $draftStatusListService;

    /** @var DraftFacade */
    private $draftFacade;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;
    /**
     * @var ProductModelDraftNormalizer
     */
    private $productModelDraftNormalizer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductDraftNormalizer $productDraftNormalizer,
        ProductModelDraftNormalizer $productModelDraftNormalizer,
        DraftStatusTranslatorService $draftStatusTranslatorService,
        DraftStatusListService $draftStatusListService,
        DraftFacade $draftFacade,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->entityManager = $entityManager;
        $this->productDraftNormalizer = $productDraftNormalizer;
        $this->draftStatusTranslatorService = $draftStatusTranslatorService;
        $this->draftStatusListService = $draftStatusListService;
        $this->draftFacade = $draftFacade;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->productModelDraftNormalizer = $productModelDraftNormalizer;
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_list")
     */
    public function getList(Request $request): JsonResponse
    {
        $criteria = [
            'status' => $request->query->get('status') ?? AbstractDraft::STATUS_NEW,
        ];
        $draftRepository = $this->entityManager->getRepository(AbstractDraft::class);

        $drafts = $draftRepository->findBy($criteria);

        $serializer = new Serializer([$this->productDraftNormalizer, $this->productModelDraftNormalizer]);
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
                'id'   => $id,
                'name' => $this->draftStatusTranslatorService->getNameTranslated($id),
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
    public function rejectDraft(AbstractProductDraft $draft): JsonResponse
    {
        if (!$draft) {
            throw new NotFoundHttpException('The draft does not exist');
        }
        if (AbstractDraft::STATUS_NEW !== $draft->getStatus()) {
            throw new BadRequestHttpException("You can only reject draft of status 'new'");
        }
        $this->draftFacade->rejectDraft($draft);

        return new JsonResponse();
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_approve")
     */
    public function approveDraft(AbstractProductDraft $draft): JsonResponse
    {
        if (!$draft) {
            throw new NotFoundHttpException('The draft does not exist');
        }
        if (AbstractDraft::STATUS_NEW !== $draft->getStatus()) {
            throw new BadRequestHttpException("You can only approve draft of status 'new'");
        }

        try {
            $this->draftFacade->approveDraft($draft);
        } catch (DraftViolationException $e) {
            $normalizedViolations = [];
            $product = $e->getProduct();
            foreach ($e->getViolations() as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['product' => $product]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        return new JsonResponse();
    }
}