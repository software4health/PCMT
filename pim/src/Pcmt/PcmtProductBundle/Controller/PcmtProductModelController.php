<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelController;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Pcmt\PcmtProductBundle\Entity\AbstractDraft;
use Pcmt\PcmtProductBundle\Entity\ExistingProductModelDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductModelDraft;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtProductModelController extends ProductModelController
{
    /** @var UserContext */
    protected $userContextProtected;

    /** @var NormalizerInterface */
    protected $normalizerProtected;

    /** @var ObjectFilterInterface */
    protected $objectFilterProtected;

    /** @var ProductModelRepositoryInterface */
    protected $productModelRepositoryProtected;

    /** @var SaverInterface */
    protected $draftSaver;

    public function setUserContextProtected(UserContext $userContextProtected): void
    {
        $this->userContextProtected = $userContextProtected;
    }

    public function setNormalizerProtected(NormalizerInterface $normalizerProtected): void
    {
        $this->normalizerProtected = $normalizerProtected;
    }

    public function setDraftSaver(SaverInterface $draftSaver): void
    {
        $this->draftSaver = $draftSaver;
    }

    public function setObjectFilterProtected(ObjectFilterInterface $objectFilterProtected): void
    {
        $this->objectFilterProtected = $objectFilterProtected;
    }

    public function setProductModelRepositoryProtected(ProductModelRepositoryInterface $productModelRepositoryProtected): void
    {
        $this->productModelRepositoryProtected = $productModelRepositoryProtected;
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        /**
         * at this stage we create NewDraft, populate it with data
         * (which we will later use to create Product Model itself) and prevent Product Model from being created.
         **/
        $draft = new NewProductModelDraft(
            $data,
            $this->userContextProtected->getUser(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->draftSaver->save($draft);

        return new JsonResponse([]);
    }

    /**
     * {@inheritdoc}
     */
    public function postAction(Request $request, $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var ProductModel $productModel */
        $productModel = $this->productModelRepositoryProtected->find($id);
        $productModel = $this->objectFilterProtected->filterObject($productModel, 'pim.internal_api.product.view') ?
            null :
            $productModel;

        if (null === $productModel) {
            throw new NotFoundHttpException(
                sprintf('ProductModel with id %s could not be found.', $id)
            );
        }

        $data = json_decode($request->getContent(), true);

        $fields = ['created', 'updated'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        $draft = new ExistingProductModelDraft(
            $productModel,
            $data,
            $this->userContextProtected->getUser(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->draftSaver->save($draft);

        return new JsonResponse($this->normalizeProductModel($productModel));
    }

    private function normalizeProductModel(ProductModelInterface $productModel): array
    {
        $normalizationContext = $this->userContextProtected->toArray() + [
            'filter_types' => [],
        ];

        return $this->normalizerProtected->normalize(
            $productModel,
            'internal_api',
            $normalizationContext
        );
    }
}