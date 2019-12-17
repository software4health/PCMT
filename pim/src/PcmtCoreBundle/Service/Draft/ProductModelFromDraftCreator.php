<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtCoreBundle\Entity\ExistingProductModelDraft;
use PcmtCoreBundle\Entity\NewProductModelDraft;
use PcmtCoreBundle\Entity\ProductModelDraftInterface;

class ProductModelFromDraftCreator
{
    /** @var ConverterInterface */
    private $productValueConverter;

    /** @var AttributeConverterInterface */
    private $localizedConverter;

    /** @var UserContext */
    private $userContext;

    /** @var FilterInterface */
    private $emptyValuesFilter;

    /** @var AttributeFilterInterface */
    private $productModelAttributeFilter;

    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    public function __construct(
        SimpleFactoryInterface $productModelFactory,
        ConverterInterface $productValueConverter,
        AttributeConverterInterface $localizedConverter,
        UserContext $userContext,
        FilterInterface $emptyValuesFilter,
        ObjectUpdaterInterface $productModelUpdater,
        AttributeFilterInterface $productModelAttributeFilter
    ) {
        $this->productValueConverter = $productValueConverter;
        $this->localizedConverter = $localizedConverter;
        $this->userContext = $userContext;
        $this->emptyValuesFilter = $emptyValuesFilter;
        $this->productModelAttributeFilter = $productModelAttributeFilter;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelFactory = $productModelFactory;
    }

    public function getProductModelToCompare(ProductModelDraftInterface $draft): ProductModelInterface
    {
        switch (get_class($draft)) {
            case NewProductModelDraft::class:
                return $this->createNewProductModel($draft);
            case ExistingProductModelDraft::class:
                return $this->createExistingProductModelForComparing($draft);
        }
    }

    public function getProductModelToSave(ProductModelDraftInterface $draft): ProductModelInterface
    {
        switch (get_class($draft)) {
            case NewProductModelDraft::class:
                return $this->createNewProductModel($draft);
            case ExistingProductModelDraft::class:
                return $this->createForSaveForDraftForExistingProductModel($draft);
        }
    }

    private function createExistingProductModelForComparing(ExistingProductModelDraft $draft): ProductModelInterface
    {
        $productModel = $draft->getProductModel();
        $newProductModel = clone $productModel;

        // cloning values, otherwise the original values would also be overwritten
        $newProductModel->setValues(new WriteValueCollection());
        foreach ($productModel->getValues() as $value) {
            $newProductModel->addValue($value);
        }
        $data = $draft->getProductData();
        if (isset($data['values'])) {
            $this->updateProductModel($newProductModel, $data);
        }

        return $newProductModel;
    }

    private function createForSaveForDraftForExistingProductModel(ExistingProductModelDraft $draft): ProductModelInterface
    {
        $productModel = $draft->getProductModel();
        $data = $draft->getProductData();
        if (isset($data['values'])) {
            $this->updateProductModel($productModel, $data);
        }

        return $productModel;
    }

    private function createNewProductModel(NewProductModelDraft $draft): ProductModelInterface
    {
        $data = $draft->getProductData();
        /** @var ProductModelInterface $productModel */
        $productModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($productModel, $data);

        return $productModel;
    }

    private function updateProductModel(ProductModelInterface $productModel, array $data): void
    {
        unset($data['parent']);
        $values = $this->productValueConverter->convert($data['values']);

        $values = $this->localizedConverter->convertToDefaultFormats($values, [
            'locale' => $this->userContext->getUiLocale()->getCode(),
        ]);

        $dataFiltered = $this->emptyValuesFilter->filter($productModel, ['values' => $values]);

        if (!empty($dataFiltered)) {
            $data = array_replace($data, $dataFiltered);
        } else {
            $data['values'] = [];
        }

        if (!$productModel->isRoot()) {
            $data = $this->productModelAttributeFilter->filter($data);
        }

        $this->productModelUpdater->update($productModel, $data);
    }
}