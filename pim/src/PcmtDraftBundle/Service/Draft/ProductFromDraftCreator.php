<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\ProductDraftInterface;

class ProductFromDraftCreator
{
    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ConverterInterface */
    private $productValueConverter;

    /** @var AttributeConverterInterface */
    private $localizedConverter;

    /** @var UserContext */
    private $userContext;

    /** @var FilterInterface */
    private $emptyValuesFilter;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    public function __construct(
        ProductBuilderInterface $productBuilder,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        UserContext $userContext,
        FilterInterface $emptyValuesFilter,
        ObjectUpdaterInterface $productUpdater,
        AttributeFilterInterface $productAttributeFilter
    ) {
        $this->productBuilder = $productBuilder;
        $this->productValueConverter = $productValueConverter;
        $this->localizedConverter = $localizedConverter;
        $this->userContext = $userContext;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->productUpdater = $productUpdater;
        $this->productAttributeFilter = $productAttributeFilter;
    }

    public function getProductToCompare(ProductDraftInterface $draft): ?ProductInterface
    {
        switch (get_class($draft)) {
            case NewProductDraft::class:
                return $this->createNewProduct($draft);
            case ExistingProductDraft::class:
                return $this->createExistingProductForComparing($draft);
            default:
                throw new \Exception('Wrong class: ' . get_class($draft));
        }
    }

    public function getProductToSave(DraftInterface $draft): ?ProductInterface
    {
        switch (get_class($draft)) {
            case NewProductDraft::class:
                return $this->createNewProduct($draft);
            case ExistingProductDraft::class:
                return $this->createForSaveForDraftForExistingProduct($draft);
        }
    }

    private function createExistingProductForComparing(ExistingProductDraft $draft): ?ProductInterface
    {
        $product = $draft->getProduct();
        if (!$product) {
            return null;
        }
        $newProduct = clone $product;

        // cloning values, otherwise the original values would also be overwritten
        $newProduct->setValues(new WriteValueCollection());
        $newProduct->setAssociations(new ArrayCollection());
        $newProduct->setCategories(new ArrayCollection());
        foreach ($product->getValuesForVariation() as $value) {
            $newProduct->addValue($value);
        }
        $data = $draft->getProductData();
        if (isset($data['values'])) {
            $this->updateProduct($newProduct, $data);
        }

        return $newProduct;
    }

    private function createForSaveForDraftForExistingProduct(ExistingProductDraft $draft): ?ProductInterface
    {
        $product = $draft->getProduct();
        if (!$product) {
            return null;
        }
        $data = $draft->getProductData();
        if (isset($data['values'])) {
            $this->updateProduct($product, $data);
        }

        return $product;
    }

    private function createNewProduct(NewProductDraft $draft): ProductInterface
    {
        $data = $draft->getProductData();

        if (isset($data['parent'])) {
            $product = $this->productBuilder->createProduct(
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

        return $product;
    }

    /**
     * Updates product with the provided data
     * Copied from ProductController
     */
    protected function updateProduct(ProductInterface $product, array $data): void
    {
        $values = $this->productValueConverter->convert($data['values']);

        $values = $this->localizedConverter->convertToDefaultFormats($values, [
            'locale' => $this->userContext->getUiLocale()->getCode(),
        ]);

        $dataFiltered = $this->emptyValuesFilter->filter($product, ['values' => $values]);

        if (!empty($dataFiltered)) {
            $data = array_replace($data, $dataFiltered);
        } else {
            $data['values'] = [];
        }

        // don't filter during creation, because identifier is needed
        // but not sent by the frontend during creation (it sends the sku in the values)
        if (null !== $product->getId() && $product->isVariant()) {
            $data = $this->productAttributeFilter->filter($data);
        }

        $this->productUpdater->update($product, $data);
    }
}