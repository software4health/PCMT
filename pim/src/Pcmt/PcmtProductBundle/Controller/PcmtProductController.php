<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Pcmt\PcmtAttributeBundle\Event\ProductFetchEvent;
use Pcmt\PcmtProductBundle\Entity\AbstractDraft;
use Pcmt\PcmtProductBundle\Entity\ExistingProductDraft;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This is skeleton of the ProductDraftController as it will serve both Drafts and Products // -it can change and be refactored
 */
class PcmtProductController extends ProductController
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var SaverInterface */
    protected $draftSaver;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        SaverInterface $draftSaver,
        ProductRepositoryInterface $productRepository,
        CursorableRepositoryInterface $cursorableRepository,
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        UserContext $userContext,
        ObjectFilterInterface $objectFilter,
        CollectionFilterInterface $productEditDataFilter,
        RemoverInterface $productRemover,
        ProductBuilderInterface $productBuilder,
        AttributeConverterInterface $localizedConverter,
        FilterInterface $emptyValuesFilter,
        ConverterInterface $productValueConverter,
        NormalizerInterface $constraintViolationNormalizer,
        ProductBuilderInterface $variantProductBuilder,
        AttributeFilterInterface $productAttributeFilter,
        ?Client $productClient = null,
        ?Client $productAndProductModelClient = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->draftSaver = $draftSaver;
        parent::__construct($productRepository, $cursorableRepository, $attributeRepository, $productUpdater, $productSaver, $normalizer, $validator, $userContext, $objectFilter, $productEditDataFilter, $productRemover, $productBuilder, $localizedConverter, $emptyValuesFilter, $productValueConverter, $constraintViolationNormalizer, $variantProductBuilder, $productAttributeFilter, $productClient, $productAndProductModelClient);
    }

    public function getAction($id): JsonResponse
    {
        $event = new ProductFetchEvent($id);
        if ($this->eventDispatcher->dispatch(ProductFetchEvent::class, $event)) {
            return parent::getAction($id);
        }
    }

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
}