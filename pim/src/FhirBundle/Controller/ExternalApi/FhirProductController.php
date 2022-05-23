<?php

declare(strict_types=1);

namespace FhirBundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductController;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductsQueryValidator;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use FhirBundle\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * Copyright (c) 2022, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
class FhirProductController extends ProductController
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var PaginatorInterface */
    protected $offsetPaginator;

    /** @var PaginatorInterface */
    protected $searchAfterPaginator;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var RemoverInterface */
    protected $remover;

    /** @var SaverInterface */
    protected $saver;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var FilterInterface */
    protected $emptyValuesFilter;

    /** @var StreamResourceResponse */
    protected $partialUpdateStreamResource;

    /** @var PrimaryKeyEncrypter */
    protected $primaryKeyEncrypter;

    /** @var [] */
    protected $apiConfiguration;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $fromSizePqbFactory;

    /** @var ProductBuilderInterface */
    protected $variantProductBuilder;

    /** @var AddParent */
    protected $addParent;

    /** @var AttributeFilterInterface */
    protected $productAttributeFilter;

    /** @var ListProductsQueryValidator */
    private $listProductsQueryValidator;

    /** @var ListProductsQueryHandler */
    private $listProductsQueryHandler;

    /** @var ConnectorProductNormalizer */
    private $connectorProductNormalizer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GetConnectorProducts */
    private $getConnectorProducts;

    /** @var DuplicateValueChecker */
    protected $duplicateValueChecker;

    public function __construct(
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $productRepository,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
        ValidatorInterface $productValidator,
        ProductBuilderInterface $productBuilder,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        UrlGeneratorInterface $router,
        FilterInterface $emptyValuesFilter,
        StreamResourceResponse $partialUpdateStreamResource,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductBuilderInterface $variantProductBuilder,
        AttributeFilterInterface $productAttributeFilter,
        AddParent $addParent,
        ListProductsQueryValidator $listProductsQueryValidator,
        array $apiConfiguration,
        ListProductsQueryHandler $listProductsQueryHandler,
        ConnectorProductNormalizer $connectorProductNormalizer,
        TokenStorageInterface $tokenStorage,
        GetConnectorProducts $getConnectorProducts,
        ?DuplicateValueChecker $duplicateValueChecker = null // TODO @merge Remove this null parameter and the conditions
    ) {
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->offsetPaginator = $offsetPaginator;
        $this->searchAfterPaginator = $searchAfterPaginator;
        $this->productValidator = $productValidator;
        $this->productBuilder = $productBuilder;
        $this->remover = $remover;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->router = $router;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->partialUpdateStreamResource = $partialUpdateStreamResource;
        $this->primaryKeyEncrypter = $primaryKeyEncrypter;
        $this->fromSizePqbFactory = $fromSizePqbFactory;
        $this->variantProductBuilder = $variantProductBuilder;
        $this->apiConfiguration = $apiConfiguration;
        $this->productAttributeFilter = $productAttributeFilter;
        $this->addParent = $addParent;
        $this->listProductsQueryValidator = $listProductsQueryValidator;
        $this->listProductsQueryHandler = $listProductsQueryHandler;
        $this->connectorProductNormalizer = $connectorProductNormalizer;
        $this->tokenStorage = $tokenStorage;
        $this->getConnectorProducts = $getConnectorProducts;
        $this->duplicateValueChecker = $duplicateValueChecker;
    }
    /**
     * @throws ServerErrorResponseException
     * @throws UnprocessableEntityHttpException
     */
    public function listAction(Request $request): JsonResponse
    {
        $query = new ListProductsQuery();

        if ($request->query->has('attributes')) {
            $query->attributeCodes = explode(',', $request->query->get('attributes'));
        }
        if ($request->query->has('locales')) {
            $query->localeCodes = explode(',', $request->query->get('locales'));
        }
        if ($request->query->has('search')) {
            $query->search = json_decode($request->query->get('search'), true);
            if (!is_array($query->search)) {
                throw new BadRequestHttpException('Search query parameter should be valid JSON.');
            }
        }

        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        $query->channelCode = $request->query->get('scope', null);
        $query->limit = $request->query->get('limit', $this->apiConfiguration['pagination']['limit_by_default']);
        $query->paginationType = $request->query->get('pagination_type', PaginationTypes::OFFSET);
        $query->searchLocaleCode = $request->query->get('search_locale', null);
        $query->withCount = $request->query->get('with_count', 'false');
        $query->page = $request->query->get('page', 1);
        $query->searchChannelCode = $request->query->get('search_scope', null);
        $query->searchAfter = $request->query->get('search_after', null);
        $query->userId = $user->getId();

        try {
            $this->listProductsQueryValidator->validate($query);
            $products = $this->listProductsQueryHandler->handle($query); // in try block as PQB is doing validation also
        } catch (InvalidQueryException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (ServerErrorResponseException $e) {
            $message = json_decode($e->getMessage(), true);
            if (null !== $message && isset($message['error']['root_cause'][0]['type'])
                && 'query_phase_execution_exception' === $message['error']['root_cause'][0]['type']) {
                throw new DocumentedHttpException(
                    Documentation::URL_DOCUMENTATION . 'pagination.html#search-after-type',
                    'You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead',
                    $e
                );
            }

            throw new ServerErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return new JsonResponse($this->normalizeProductsList($products, $query));
    }
    /**
     * @throws NotFoundHttpException
     */
    public function getAction(string $code): JsonResponse
    {
        try {
            $user = $this->tokenStorage->getToken()->getUser();
            Assert::isInstanceOf($user, UserInterface::class);

            $product = $this->getConnectorProducts->fromProductIdentifier($code, $user->getId());
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $normalizedProduct = $this->connectorProductNormalizer->normalizeConnectorProduct($product);

        return new JsonResponse($normalizedProduct);
    }

    private function normalizeProductsList(ConnectorProductList $connectorProductList, ListProductsQuery $query): array
    {
        $queryParameters = [
            'with_count'      => $query->withCount,
            'pagination_type' => $query->paginationType,
            'limit'           => $query->limit,
        ];

        if ([] !== $query->search) {
            $queryParameters['search'] = json_encode($query->search);
        }
        if (null !== $query->channelCode) {
            $queryParameters['scope'] = $query->channelCode;
        }
        if (null !== $query->searchChannelCode) {
            $queryParameters['search_scope'] = $query->searchChannelCode;
        }
        if (null !== $query->localeCodes) {
            $queryParameters['locales'] = implode(',', $query->localeCodes);
        }
        if (null !== $query->attributeCodes) {
            $queryParameters['attributes'] = implode(',', $query->attributeCodes);
        }

        if (PaginationTypes::OFFSET === $query->paginationType) {
            $queryParameters = ['page' => $query->page] + $queryParameters;

            $paginationParameters = [
                'query_parameters'    => $queryParameters,
                'list_route_name'     => 'pim_fhir_api_product_list',
                'item_route_name'     => 'pim_fhir_api_product_get',
                'item_identifier_key' => 'id',
            ];

            $count = $query->withCountAsBoolean() ? $connectorProductList->totalNumberOfProducts() : null;

            return $this->offsetPaginator->paginate(
                $this->connectorProductNormalizer->normalizeConnectorProductList($connectorProductList),
                $paginationParameters,
                $count
            );
        }
        $connectorProducts = $connectorProductList->connectorProducts();
        $lastProduct = end($connectorProducts);

        $parameters = [
            'query_parameters' => $queryParameters,
            'search_after'     => [
                'next' => false !== $lastProduct ? $this->primaryKeyEncrypter->encrypt($lastProduct->id()) : null,
                'self' => $query->searchAfter,
            ],
            'list_route_name'     => 'pim_fhir_api_product_list',
            'item_route_name'     => 'pim_fhir_api_product_get',
            'item_identifier_key' => 'id',
        ];

        return $this->searchAfterPaginator->paginate(
            $this->connectorProductNormalizer->normalizeConnectorProductList($connectorProductList),
            $parameters,
            null
        );
    }
}