<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Malawi;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProcessorCopier;
use Ramsey\Uuid\Uuid;

class RuleProductProcessor
{
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

    /** @var ProductBuilderInterface */
    private $productModelUpdater;

    public function __construct(
        RuleAttributeProvider $ruleAttributeProvider,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        ProductBuilderInterface $variantProductBuilder,
        ProductModelUpdater $productModelUpdater,
        RuleProcessorCopier $ruleProcessorCopier
    ) {
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->variantProductBuilder = $variantProductBuilder;
        $this->productModelUpdater = $productModelUpdater;
        $this->ruleProcessorCopier = $ruleProcessorCopier;
    }

    public function process(Rule $rule, ProductInterface $sourceProduct): void
    {
        $keyValue = $sourceProduct->getValue($rule->getKeyAttribute()->getCode());
        if (!$keyValue) {
            return;
        }

        $associations = $sourceProduct->getAssociations();
        foreach ($associations as $association) {
            $models = $association->getProductModels();
            foreach ($models as $model) {
                /** @var ProductModelInterface $model */
                echo 'Found product model association: ' . $model->getCode() . "\n";

                $this->processDestinationProductModel($rule, $sourceProduct, $model);
            }
        }
    }

    private function processDestinationProductModel(Rule $rule, ProductInterface $sourceProduct, ProductModelInterface $destinationProductModel): void
    {
        $attributes = $this->ruleAttributeProvider->getAllForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());

        $sourceKeyAttributeValue = $sourceProduct->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_FIRST_AXIS);
        echo 'key attribute value: ' . $sourceKeyAttributeValue->getData() . "\n";
        $subProductModels = $destinationProductModel->getProductModels();
        foreach ($subProductModels as $subProductModel) {
            /** @var ProductModelInterface $subProductModel */
            $destinationKeyAttributeValue = $subProductModel->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_FIRST_AXIS);

            echo '- found sub product model: ' . $destinationKeyAttributeValue->getData() . "\n";

            if ($sourceKeyAttributeValue->getData() === $destinationKeyAttributeValue->getData()) {
                echo "Matching sub product model exists, copying data.\n";
                $this->copy($sourceProduct, $subProductModel, $attributes);
                $this->processDestinationSubProductModel($rule, $sourceProduct, $subProductModel);

                return;
            }
        }

        echo "Sub product does not exists, creating.\n";
        $subProductModel = $this->createNewDestinationProductModel($sourceProduct, $destinationProductModel, $attributes);
        $this->processDestinationSubProductModel($rule, $sourceProduct, $subProductModel);
    }

    private function processDestinationSubProductModel(Rule $rule, ProductInterface $sourceProduct, ProductModelInterface $destinationSubProductModel): void
    {
        $attributes = $this->ruleAttributeProvider->getAllForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());
        $sourceKeyAttributeValue = $sourceProduct->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_SECOND_AXIS_SOURCE);
        $destinationProducts = $destinationSubProductModel->getProducts();
        foreach ($destinationProducts as $product) {
            /** @var ProductInterface $product */
            $value = $product->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME_SECOND_AXIS_DESTINATION);
            echo '- found variant: '. $value->getData()."\n";

            if ($sourceKeyAttributeValue->getData() === $value->getData()) {
                echo "Matching variant exists, copying data.\n";
                $this->copy($sourceProduct, $product, $attributes);

                return;
            }
        }

        echo "Variant not exists, creating.\n";
        $this->createNewDestinationProduct($sourceProduct, $destinationSubProductModel, $attributes);
    }

    private function copy(
        ProductInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        array $attributes
    ): void {
        try {
            $result = $this->ruleProcessorCopier->copy($sourceProduct, $destinationProduct, $attributes);
            if ($result) {
                if ($destinationProduct instanceof ProductInterface) {
                    $this->productSaver->save($destinationProduct);
                } else {
                    $this->productModelSaver->saveAll([$destinationProduct]);
                }
            }
        } catch (\Throwable $e) {
            echo sprintf(
                "- error while copying %d: %d\n",
                $sourceProduct->getLabel(),
                $e->getMessage()
            );
        }
    }

    private function createNewDestinationProduct(ProductInterface $sourceProduct, ProductModelInterface $productModel, array $attributes): void
    {
        $destinationProduct = $this->variantProductBuilder->createProduct(
            Uuid::uuid4()->toString(),
            $productModel->getFamily()->getCode()
        );

        $variants = $productModel->getFamily()->getFamilyVariants();
        $variant = $variants->first();
        $destinationProduct->setFamilyVariant($variant);
        $destinationProduct->setParent($productModel);
        $this->copy($sourceProduct, $destinationProduct, $attributes);
    }

    private function createNewDestinationProductModel(ProductInterface $sourceProduct, ProductModelInterface $productModel, array $attributes): ProductModelInterface
    {
        $subProductModel = new ProductModel();
        $subProductModel->setCreated(new \DateTime());
        $subProductModel->setUpdated(new \DateTime());

        $data = [
            'code' => Uuid::uuid4()->toString(),
        ];

        $this->productModelUpdater->update($subProductModel, $data);

        $variants = $productModel->getFamily()->getFamilyVariants();
        $variant = $variants->first();
        $subProductModel->setFamilyVariant($variant);
        $subProductModel->setParent($productModel);
        $this->copy($sourceProduct, $subProductModel, $attributes);

        return $subProductModel;
    }
}