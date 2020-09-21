<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use PcmtCoreBundle\Connector\Job\InvalidItems\SimpleInvalidItem;
use PcmtRulesBundle\Entity\Rule;

class RuleProductProcessor
{
    /** @var ProductQueryBuilderFactory */
    private $pqbFactory;

    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    /** @var PropertyCopierInterface */
    private $propertyCopier;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var ProductInterface[] */
    private $productsToSave = [];

    /** @var ProductModelInterface[] */
    private $productModelsToSave = [];

    public function __construct(
        ProductQueryBuilderFactory $pqbFactory,
        RuleAttributeProvider $ruleAttributeProvider,
        PropertyCopierInterface $propertyCopier,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->propertyCopier = $propertyCopier;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
    }

    public function process(StepExecution $stepExecution, Rule $rule, EntityWithValuesInterface $sourceProduct): void
    {
        $this->productsToSave = [];
        $this->productModelsToSave = [];

        $attributes = $this->ruleAttributeProvider->getForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());

        $keyValue = $sourceProduct->getValue($rule->getKeyAttribute()->getCode());
        if (!$keyValue) {
            return;
        }
        // searching through ElasticSearch index
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
        ]);

        try {
            $pqb->addFilter($rule->getKeyAttribute()->getCode(), Operators::EQUALS, $keyValue->getData());
        } catch (\Throwable $e) {
            try {
                $pqb->addFilter($rule->getKeyAttribute()->getCode(), Operators::IN_LIST, [$keyValue->getData()]);
            } catch (\Throwable $e) {
                throw new \Exception('Unsupported attribute: ' . $rule->getKeyAttribute()->getCode(). ' Details: '. $e->getMessage());
            }
        }

        $pqb->addFilter('family', Operators::IN_LIST, [$rule->getDestinationFamily()->getCode()]);

        $destinationProducts = $pqb->execute();
        foreach ($destinationProducts as $destinationProduct) {
            foreach ($attributes as $attribute) {
                /** @var AttributeInterface $attribute */
                try {
                    if ('pim_catalog_identifier' !== $attribute->getType()) {
                        $this->copyData($sourceProduct, $destinationProduct, $attribute->getCode());
                    }
                } catch (\Throwable $e) {
                    $msg = sprintf(
                        'Problem with copying data from product %s to product %s, attribute: %s. Error: %s',
                        $sourceProduct->getId(),
                        $destinationProduct->getId(),
                        $attribute->getLabel(),
                        $e->getMessage()
                    );
                    $invalidItem = new SimpleInvalidItem(
                        [
                            'sourceProduct'      => $sourceProduct->getIdentifier(),
                            'destinationProduct' => $destinationProduct->getIdentifier(),
                            'attribute'          => $attribute->getCode(),
                        ]
                    );
                    $stepExecution->addWarning($msg, [], $invalidItem);
                }
            }
        }

        foreach ($this->productsToSave as $product) {
            $this->productSaver->save($product);
            $stepExecution->incrementSummaryInfo('destination_products_found_and_saved', 1);
        }
        foreach ($this->productModelsToSave as $productModel) {
            $this->productModelSaver->save($productModel);
            $stepExecution->incrementSummaryInfo('destination_product_models_found_and_saved', 1);
        }
    }

    private function copyData(EntityWithValuesInterface $sourceProduct, EntityWithValuesInterface $destinationProduct, string $attributeCode): void
    {
        if ($destinationProduct instanceof ProductInterface) {
            $this->productsToSave[$destinationProduct->getId()] = $destinationProduct;
        } elseif ($destinationProduct instanceof ProductModelInterface) {
            $this->productModelsToSave[$destinationProduct->getId()] = $destinationProduct;
        }

        $this->propertyCopier->copyData(
            $sourceProduct,
            $destinationProduct,
            $attributeCode,
            $attributeCode
        );

        if ($destinationProduct->getParent()) {
            $this->copyData($sourceProduct, $destinationProduct->getParent(), $attributeCode);
        }
    }
}