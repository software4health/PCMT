<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\MassEditAction\OperationJobLauncher;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Exception\DraftSavingFailedException;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\MassActions\DraftsBulkActionOperation;
use PcmtDraftBundle\Normalizer\DraftViolationNormalizer;
use PcmtDraftBundle\Repository\DraftRepositoryInterface;
use PcmtDraftBundle\Service\Builder\ResponseBuilder;
use PcmtDraftBundle\Service\Draft\DraftFacade;
use PcmtDraftBundle\Service\Draft\DraftStatusListService;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DraftController
{
    /** @var DraftStatusListService */
    private $draftStatusListService;

    /** @var DraftFacade */
    private $draftFacade;

    /** @var ResponseBuilder */
    private $responseBuilder;

    /** @var OperationJobLauncher */
    private $operationJobLauncher;

    /** @var DraftRepositoryInterface */
    private $draftRepository;

    /** @var DraftViolationNormalizer */
    private $draftViolationNormalizer;

    /** @var GeneralObjectFromDraftCreator */
    private $creator;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    public function __construct(
        DraftStatusListService $draftStatusListService,
        DraftFacade $draftFacade,
        ResponseBuilder $responseBuilder,
        OperationJobLauncher $operationJobLauncher,
        DraftRepositoryInterface $draftRepository,
        DraftViolationNormalizer $draftViolationNormalizer,
        GeneralObjectFromDraftCreator $creator,
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker
    ) {
        $this->draftStatusListService = $draftStatusListService;
        $this->draftFacade = $draftFacade;
        $this->responseBuilder = $responseBuilder;
        $this->operationJobLauncher = $operationJobLauncher;
        $this->draftRepository = $draftRepository;
        $this->draftViolationNormalizer = $draftViolationNormalizer;
        $this->creator = $creator;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_list")
     */
    public function getList(Request $request): JsonResponse
    {
        $statusId = $request->query->get('status') ?? AbstractDraft::STATUS_NEW;
        $statusId = (int) $statusId;

        $page = $request->query->get('page') ?? ResponseBuilder::FIRST_PAGE;
        $total = $this->draftRepository->countWithStatus($statusId);
        $lastPage = $this->responseBuilder->getLastPage($total);
        $page = $page > $lastPage ? $lastPage : $page;

        $drafts = $this->draftRepository->findWithStatus(
            $statusId,
            ($page * ResponseBuilder::PER_PAGE) - ResponseBuilder::PER_PAGE,
            ResponseBuilder::PER_PAGE
        );

        return $this->responseBuilder->buildPaginatedResponse($drafts, $total, (int) $page);
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_list")
     */
    public function getDraft(AbstractDraft $draft): Response
    {
        $this->hasAccessOr403(
            $draft,
            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
            'pcmt.exception.permission.denied.edit'
        );

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
        $this->hasAccessOr403(
            $draft,
            CategoryPermissionsCheckerInterface::EDIT_LEVEL,
            'pcmt.exception.permission.denied.edit'
        );

        $data = json_decode($request->getContent(), true);
        if (($draft instanceof ExistingProductDraft || $draft instanceof ExistingProductModelDraft) && !isset($data['product'])) {
            throw new BadRequestHttpException('There is no product values');
        }

        if ($draft instanceof ProductModelDraftInterface && isset($data['product']['family'])) {
            unset($data['product']['family']);
        }

        if ($draft instanceof ProductModelDraftInterface && !empty($data['parentId'])) {
            unset($data['family']);
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

        try {
            $options = [];
            if (isset($data['lastUpdatedAtTimestamp'])) {
                $options['lastUpdatedAt'] = $data['lastUpdatedAtTimestamp'];
            }
            $this->draftFacade->updateDraft($draft, $options);
        } catch (DraftViolationException $e) {
            return new JsonResponse(
                ['values' => $this->draftViolationNormalizer->normalize($e)],
                Response::HTTP_BAD_REQUEST
            );
        } catch (DraftSavingFailedException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

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
        return new JsonResponse(['statuses' => $this->draftStatusListService->getTranslated()]);
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_reject")
     */
    public function rejectDraft(AbstractDraft $draft): JsonResponse
    {
        try {
            $this->hasAccessOr403(
                $draft,
                CategoryPermissionsCheckerInterface::OWN_LEVEL,
                'pcmt.exception.permission.denied.own'
            );

            $this->draftFacade->rejectDraft($draft);
        } catch (\Throwable $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse();
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_approve")
     */
    public function approveDraft(AbstractDraft $draft): JsonResponse
    {
        try {
            $this->hasAccessOr403(
                $draft,
                CategoryPermissionsCheckerInterface::OWN_LEVEL,
                'pcmt.exception.permission.denied.own'
            );

            $this->draftFacade->approveDraft($draft);
        } catch (DraftViolationException $e) {
            return new JsonResponse(
                ['values' => $this->draftViolationNormalizer->normalize($e)],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Throwable $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse();
    }

    /**
     * @AclAncestor("pcmt_permission_drafts_approve")
     */
    public function actionsBulkDraft(Request $request): JsonResponse
    {
        $chosenDrafts = json_decode($request->getContent(), true)['chosenDrafts'];
        $jobInstanceCode = json_decode($request->getContent(), true)['jobInstanceCode'];

        $operation = new DraftsBulkActionOperation(
            $jobInstanceCode,
            $chosenDrafts[DraftsBulkActionOperation::KEY_ALL_SELECTED] ?? false,
            $chosenDrafts[DraftsBulkActionOperation::KEY_SELECTED] ?? [],
            $chosenDrafts[DraftsBulkActionOperation::KEY_EXCLUDED] ?? []
        );
        $this->operationJobLauncher->launch($operation);

        return new JsonResponse();
    }

    protected function hasAccessOr403(AbstractDraft $draft, string $level, string $message): void
    {
        $objectToSave = $this->creator->getObjectToSave($draft);
        if (!$this->categoryPermissionsChecker->hasAccessToProduct($level, $objectToSave)) {
            throw new AccessDeniedHttpException($message);
        }
    }
}