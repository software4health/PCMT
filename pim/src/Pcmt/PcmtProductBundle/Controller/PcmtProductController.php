<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM\VersionRepository;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Pcmt\PcmtProductBundle\Entity\ProductDraftHistory;
use Pcmt\PcmtProductBundle\Entity\DraftHistoryInterface;
use Pcmt\PcmtProductBundle\Entity\ProductDraftInterface;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Pcmt\PcmtAttributeBundle\Event\ProductFetchEvent;

/**
 * This is skeleton of the ProductDraftController as it will serve both Drafts and Products // -it can change and be refactored
 */
class PcmtProductController extends ProductController
{
    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var VersionRepository $versionRepository */
    protected $versionRepository;

    /** @var SaverInterface $draftSaver */
    protected $draftSaver;

    /** @var NormalizerInterface $draftNormalizer */
    protected $draftNormalizer;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        VersionRepository $versionRepository,
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
        Client $productClient = null,
        Client $productAndProductModelClient = null
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->versionRepository = $versionRepository;
        $this->draftSaver = $draftSaver;
        parent::__construct($productRepository, $cursorableRepository, $attributeRepository, $productUpdater, $productSaver, $normalizer, $validator, $userContext, $objectFilter, $productEditDataFilter, $productRemover, $productBuilder, $localizedConverter, $emptyValuesFilter, $productValueConverter, $constraintViolationNormalizer, $variantProductBuilder, $productAttributeFilter, $productClient, $productAndProductModelClient);
    }

    public function getAction($id)
    {
        $event = new ProductFetchEvent($id);
        if($this->eventDispatcher->dispatch(ProductFetchEvent::class, $event))

            return parent::getAction($id);
    }


    public function createAction(Request $request): ?JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

         /**
          * at this stage we create NewDraft, populate it with data (which we will later use to create Product itself)
          * and prevent Product from being created.
          * Later, in the DraftController, in approve action, we will check the draft type.
          * if NewDraft,then create Product, and create PendingDraft.
         **/

         $draft = new NewProductDraft(
             $data,
             $this->userContext->getUser(),
             new \DateTime(),
             ProductDraftInterface::DRAFT_VERSION_NEW,
             ProductDraftInterface::STATUS_NEW
         );
         $productHistory = new ProductDraftHistory(
            new \DateTime(),
             $draft->getAuthor(),
            [ DraftHistoryInterface::PRODUCT_DRAFT_CREATED ]
        );

        $draft->addDraftHistory($productHistory);
        $this->draftSaver->save($draft);

        return new JsonResponse($this->normalizer->normalize(
            $draft,
            'internal_api',
            $this->getNormalizationContext()
        ));

        if (isset($data['parent'])) {
            $product = $this->variantProductBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );

            if (isset($data['values'])) {
                $this->updateProduct($product, $data);
            }
        } else {
            $product = $this->productBuilder->createProduct(
                $data['identifier'] ?? null,
                $data['family'] ?? null
            );
        }

        $violations = $this->validator->validate($product);

        if (0 === $violations->count()) {
            $this->productSaver->save($product);


            return new JsonResponse($this->normalizer->normalize(
                $product,
                'internal_api',
                $this->getNormalizationContext()
            ));
        }

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['product' => $product]
            );
        }

        return new JsonResponse(['values' => $normalizedViolations], 400);
    }

    public function approveAction(Request $request, int $id): JsonResponse
    {
        if(!$this->userContext->getUser()->hasRole('Catalog_Manager')){
            return new JsonResponse($request, Response::HTTP_UNAUTHORIZED);
        }

        $draft = $this->draftRepository->find($id);
        if(!$draft){
            throw new NotFoundHttpException(
                sprintf('Product Draft with id %s could not be found.', $id)
            );
        }

        /**
         * @todo approval logic
         */
        $product = $this->draftManager->approveDraft($draft);

        return new JsonResponse($this->normalizer->normalize(
            $product,
            'internal_api',
            $this->getNormalizationContext()
        ));
    }
}