<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Entity\AbstractDraft;
use PcmtCoreBundle\Entity\ExistingProductDraft;
use PcmtCoreBundle\Entity\NewProductDraft;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PcmtProductController extends ProductController
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var SaverInterface */
    protected $draftSaver;

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
            $this->userContext->getUser(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->draftSaver->save($draft);

        return new JsonResponse($this->normalizer->normalize(
            $draft,
            'internal_api',
            $this->getNormalizationContext()
        ));
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

        $fields = ['created', 'updated'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        $draft = new ExistingProductDraft(
            $product,
            $data,
            $this->userContext->getUser(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->draftSaver->save($draft);

        return new JsonResponse($this->normalizer->normalize(
            $product,
            'internal_api',
            $this->getNormalizationContext()
        ));
    }

    public function setDraftSaver(SaverInterface $draftSaver): void
    {
        $this->draftSaver = $draftSaver;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}