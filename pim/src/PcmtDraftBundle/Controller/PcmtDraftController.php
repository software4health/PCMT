<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PcmtCoreBundle\Service\Builder\ResponseBuilder;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Service\Draft\DraftFacade;
use PcmtDraftBundle\Service\Draft\DraftStatusListService;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtDraftController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var DraftStatusTranslatorService */
    private $draftStatusTranslatorService;

    /** @var DraftStatusListService */
    private $draftStatusListService;

    /** @var DraftFacade */
    private $draftFacade;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /** @var ResponseBuilder */
    protected $responseBuilder;

    public function __construct(
        EntityManagerInterface $entityManager,
        DraftStatusTranslatorService $draftStatusTranslatorService,
        DraftStatusListService $draftStatusListService,
        DraftFacade $draftFacade,
        NormalizerInterface $constraintViolationNormalizer,
        ResponseBuilder $responseBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->draftStatusTranslatorService = $draftStatusTranslatorService;
        $this->draftStatusListService = $draftStatusListService;
        $this->draftFacade = $draftFacade;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->responseBuilder = $responseBuilder;
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

        $page = $request->query->get('page') ?? ResponseBuilder::FIRST_PAGE;
        $drafts = $draftRepository->findBy(
            $criteria,
            null,
            ResponseBuilder::PER_PAGE,
            ($page * ResponseBuilder::PER_PAGE) - ResponseBuilder::PER_PAGE
        );
        $total = $draftRepository->count($criteria);

        return $this->responseBuilder->buildPaginatedResponse($drafts, $total, (int) $page);
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_list")
     */
    public function getDraft(AbstractDraft $draft): Response
    {
        if (!$draft) {
            throw new NotFoundHttpException('The draft does not exist');
        }

        return $this->responseBuilder
            ->setData($draft)
            ->setContext(['include_product' => true])
            ->build();
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_edit")
     */
    public function updateDraft(AbstractDraft $draft, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (($draft instanceof ExistingProductDraft || $draft instanceof ExistingProductModelDraft) && !isset($data['product'])) {
            throw new BadRequestHttpException('There is no product values');
        }

        if ($draft instanceof ProductModelDraftInterface && isset($data['product']['family'])) {
            unset($data['product']['family']);
        }
        if (isset($data['categories'])) {
            $data['product']['categories'] = $data['categories'];
        }

        if ($draft instanceof ExistingProductDraft || $draft instanceof ExistingProductModelDraft) {
            $draft->setProductData($data['product']);
        } else {
            unset($data['draftId'], $data['parentId']);
            $draft->setProductData($data);
        }

        $this->draftFacade->updateDraft($draft);

        $responseBuilder = $this->responseBuilder
            ->setData($draft);

        if ($draft instanceof ExistingProductDraft || $draft instanceof ExistingProductModelDraft) {
            $responseBuilder = $responseBuilder->setContext(['include_product' => true]);
        }

        return $responseBuilder->build();
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
    public function rejectDraft(AbstractDraft $draft): JsonResponse
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
    public function approveDraft(AbstractDraft $draft): JsonResponse
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
            $context = [];
            if ($e->getProduct()) {
                $context['product'] = $e->getProduct();
            }
            if ($e->getProductModel()) {
                $context['productModel'] = $e->getProductModel();
            }
            foreach ($e->getViolations() as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    $context
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        return new JsonResponse();
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_approve")
     */
    public function approveBulkDraft(Request $request): JsonResponse
    {
        $draftRepository = $this->entityManager->getRepository(AbstractDraft::class);
        $chosenDrafts = json_decode($request->getContent(), true)['chosenDrafts'];

        $normalizedViolations = [];
        foreach ($chosenDrafts as $draftId) {
            $draft = $draftRepository->find($draftId);
            try {
                $this->draftFacade->approveDraft($draft);
            } catch (DraftViolationException $e) {
                $context = [];
                if ($e->getProduct()) {
                    $context['product'] = $e->getProduct();
                }
                if ($e->getProductModel()) {
                    $context['productModel'] = $e->getProductModel();
                }
                foreach ($e->getViolations() as $violation) {
                    $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                        $violation,
                        'internal_api',
                        $context
                    );
                }
            }
        }
        if ($normalizedViolations) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        return new JsonResponse();
    }
}