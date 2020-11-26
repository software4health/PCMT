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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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

    public function __construct(
        RuleAttributeProvider $ruleAttributeProvider,
        SaverInterface $productSaver,
        ProductBuilderInterface $variantProductBuilder,
        RuleProcessorCopier $ruleProcessorCopier
    ) {
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->productSaver = $productSaver;
        $this->variantProductBuilder = $variantProductBuilder;
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

        $sourceKeyAttributeValue = $sourceProduct->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME);
        echo 'key attribute value: '. $sourceKeyAttributeValue->getData() . "\n";
        $products = $destinationProductModel->getProducts();
        foreach ($products as $product) {
            /** @var ProductInterface $product */
            $value = $product->getValue(RuleProcessStep::KEY_ATTRIBUTE_NAME);
            echo 'Found variant: '. $value->getData()."\n";

            if ($sourceKeyAttributeValue->getData() === $value->getData()) {
                echo "Variant exists, copying data.\n";
                $this->copy($sourceProduct, $product, $attributes);

                return;
            }
        }

        echo "Variant not exists, creating.\n";
        $this->createNewDestinationProduct($sourceProduct, $destinationProductModel, $attributes);
    }

    private function copy(
        ProductInterface $sourceProduct,
        ProductInterface $destinationProduct,
        array $attributes
    ): void {
        try {
            $result = $this->ruleProcessorCopier->copy($sourceProduct, $destinationProduct, $attributes);
            if ($result) {
                $this->productSaver->save($destinationProduct);
            }
        } catch (\Throwable $e) {
            echo 'Error while copying '. $sourceProduct->getLabel() . "\n";
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
}