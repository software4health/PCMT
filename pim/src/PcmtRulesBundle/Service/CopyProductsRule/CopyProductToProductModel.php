<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service\CopyProductsRule;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use Ramsey\Uuid\Uuid;

class CopyProductToProductModel
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    /** @var SaverInterface */
    private $productSaver;

    /** @var ProductBuilderInterface */
    private $variantProductBuilder;

    /** @var RuleProcessorCopier */
    private $ruleProcessorCopier;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var SubEntityFinder */
    private $subEntityFinder;

    public function __construct(
        RuleAttributeProvider $ruleAttributeProvider,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        ProductBuilderInterface $variantProductBuilder,
        RuleProcessorCopier $ruleProcessorCopier,
        SubEntityFinder $subEntityFinder
    ) {
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->variantProductBuilder = $variantProductBuilder;
        $this->ruleProcessorCopier = $ruleProcessorCopier;
        $this->subEntityFinder = $subEntityFinder;
    }

    public function process(
        StepExecution $stepExecution,
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationProductModel
    ): void {
        $this->stepExecution = $stepExecution;
        $this->processDestinationProductModel($sourceProduct, $destinationProductModel, 1);
    }

    private function processDestinationProductModel(
        ProductInterface $sourceProduct,
        ProductModelInterface $destinationProductModel,
        int $level
    ): void {
        $variant = $destinationProductModel->getFamilyVariant();
        $set = $variant->getVariantAttributeSet($level);
        /** @var VariantAttributeSetInterface $set */
        $axisAttributes = $set->getAxes();
        foreach ($axisAttributes as $attribute) {
            /** @var AttributeInterface $attribute */
            $value = $sourceProduct->getValue($attribute->getCode());
            if (!$value || !$value->getData()) {
                $this->stepExecution->incrementSummaryInfo('axis_attribute_not_exists_in_source_product', 1);

                return;
            }
        }

        $attributes = $this->ruleAttributeProvider->getAllForFamilies(
            $sourceProduct->getFamily(),
            $destinationProductModel->getFamily()
        );
        foreach ($attributes as $key => $attribute) {
            if (!$set->hasAttribute($attribute)) {
                unset($attributes[$key]);
            }
        }

        $subEntity = $this->subEntityFinder->findByAxisAttributes($destinationProductModel, $axisAttributes, $sourceProduct);
        if ($subEntity) {
            $this->stepExecution->incrementSummaryInfo('subentities_found', 1);

            $this->copy($sourceProduct, $subEntity, $attributes);
            if ($subEntity instanceof ProductModelInterface) {
                $this->processDestinationProductModel($sourceProduct, $subEntity, ++$level);
            }
        } else {
            if ($level < $variant->getNumberOfLevel()) {
                $subProductModel = $this->createNewSubProductModel(
                    $sourceProduct,
                    $destinationProductModel,
                    $attributes
                );
                $this->stepExecution->incrementSummaryInfo('sub_product_models_created', 1);
                $this->processDestinationProductModel($sourceProduct, $subProductModel, ++$level);
            } else {
                $this->createNewSubProduct(
                    $sourceProduct,
                    $destinationProductModel,
                    $attributes
                );
                $this->stepExecution->incrementSummaryInfo('sub_products_created', 1);
            }
        }
    }

    private function copy(
        ProductInterface $sourceProduct,
        EntityWithValuesInterface $destinationEntity,
        array $attributes
    ): void {
        foreach ($attributes as $key => $attribute) {
            /** @var AttributeInterface $attribute */
            if (!$sourceProduct->getValue($attribute->getCode())) {
                unset($attributes[$key]);
            }
        }

        $result = $this->ruleProcessorCopier->copy($sourceProduct, $destinationEntity, $attributes);
        if ($result) {
            if ($destinationEntity instanceof ProductInterface) {
                $this->productSaver->save($destinationEntity);
            } else {
                $this->productModelSaver->save($destinationEntity);
            }
        }
    }

    private function createNewSubProduct(
        ProductInterface $sourceProduct,
        ProductModelInterface $productModel,
        array $attributes
    ): void {
        $destinationProduct = $this->variantProductBuilder->createProduct(
            Uuid::uuid4()->toString(),
            $productModel->getFamily()->getCode()
        );

        $destinationProduct->setFamilyVariant($productModel->getFamilyVariant());
        $destinationProduct->setParent($productModel);
        $this->copy($sourceProduct, $destinationProduct, $attributes);
    }

    private function createNewSubProductModel(
        ProductInterface $sourceProduct,
        ProductModelInterface $productModel,
        array $attributes
    ): ProductModelInterface {
        $subProductModel = new ProductModel();
        $subProductModel->setCreated(new \DateTime());
        $subProductModel->setUpdated(new \DateTime());
        $subProductModel->setCode(Uuid::uuid4()->toString());
        $subProductModel->setFamilyVariant($productModel->getFamilyVariant());
        $subProductModel->setParent($productModel);
        $this->copy($sourceProduct, $subProductModel, $attributes);

        return $subProductModel;
    }
}
