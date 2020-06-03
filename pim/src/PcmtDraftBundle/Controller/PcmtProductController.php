<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Service\Builder\ResponseBuilder;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Code copied from Product Controller:
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PcmtProductController extends ProductController
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var SaverInterface */
    protected $draftSaver;

    /** @var ResponseBuilder */
    private $responseBuilder;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        /**
         * at this stage we create NewDraft, populate it with data (which we will later use to create Product itself)
         * and prevent Product from being created.
         **/
        $draft = new NewProductDraft(
            $data,
            new \DateTime(),
            $this->userContext->getUser()
        );

        try {
            $this->draftSaver->save($draft);
        } catch (DraftViolationException $e) {
            $normalizedViolations = [];
            $context = $e->getContextForNormalizer();
            foreach ($e->getViolations() as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    $context
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        return $this->responseBuilder->setData($draft)->setContext($this->getNormalizationContext())->build();
    }

    /**
     * {@inheritdoc}
     */
    public function getAction($id)
    {
        $product = $this->findProductOr404($id);

        $this->hasAccessOr403($product);

        return $this->responseBuilder->setData($product)
            ->setContext($this->getNormalizationContext() + ['include_draft_id' => true])
            ->setFormat('internal_api')
            ->build();
    }

    /**
     * {@inheritdoc}
     */
    public function postAction(Request $request, $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($id);
        if ($this->objectFilter->filterObject($product, 'pim.internal_api.product.edit')) {
            throw new AccessDeniedHttpException();
        }
        $data = json_decode($request->getContent(), true);

        try {
            $data = $this->productEditDataFilter->filterCollection($data, null, ['product' => $product]);
        } catch (ObjectNotFoundException $e) {
            throw new BadRequestHttpException();
        }

        $fields = [
            'created',
            'updated',
        ];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        $draft = new ExistingProductDraft(
            $product,
            $data,
            new \DateTime(),
            $this->userContext->getUser()
        );

        $this->draftSaver->save($draft);

        return $this->responseBuilder->setData($draft)->setFormat('internal_api')->setContext(
            $this->getNormalizationContext()
        )->build();
    }

    public function setDraftSaver(SaverInterface $draftSaver): void
    {
        $this->draftSaver = $draftSaver;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setResponseBuilder(ResponseBuilder $responseBuilder): void
    {
        $this->responseBuilder = $responseBuilder;
    }

    public function setCategoryPermissionsChecker(CategoryPermissionsCheckerInterface $categoryPermissionsChecker): void
    {
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    protected function hasAccessOr403(CategoryAwareInterface $entity): void
    {
        if (!$this->categoryPermissionsChecker->hasAccessToProduct(
            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
            $entity
        )) {
            throw new AccessDeniedHttpException('Access denied basing on categories');
        }
    }
}