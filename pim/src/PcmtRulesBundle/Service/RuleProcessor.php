<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtCoreBundle\Connector\Job\InvalidItems\SimpleInvalidItem;
use PcmtRulesBundle\Entity\Rule;
use Ramsey\Uuid\Uuid;

class RuleProcessor
{
    public const MAX_DESTINATION_PRODUCTS = 100000;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var RuleAttributeProvider */
    private $ruleAttributeProvider;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ProductInterface[] */
    private $productsToSave = [];

    /** @var int[] */
    private $destinationProductsId = [];

    /** @var ProductModelInterface[] */
    private $productModelsToSave = [];

    /** @var int[] */
    private $destinationProductModelsId = [];

    /** @var RuleProcessorCopier */
    private $ruleProcessorCopier;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        RuleAttributeProvider $ruleAttributeProvider,
        SaverInterface $productSaver,
        SaverInterface $productModelSaver,
        ProductBuilderInterface $productBuilder,
        RuleProcessorCopier $ruleProcessorCopier
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->ruleAttributeProvider = $ruleAttributeProvider;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->productBuilder = $productBuilder;
        $this->ruleProcessorCopier = $ruleProcessorCopier;
    }

    public function process(StepExecution $stepExecution, Rule $rule, EntityWithValuesInterface $sourceProduct): void
    {
        $this->productsToSave = [];
        $this->productModelsToSave = [];

        $attributes = $this->ruleAttributeProvider->getAllForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());

        $keyValue = $sourceProduct->getValue($rule->getKeyAttribute()->getCode());
        if (!$keyValue) {
            return;
        }

        // searching through ElasticSearch index
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
            'limit'          => self::MAX_DESTINATION_PRODUCTS,
        ]);
        try {
            $pqb->addFilter($rule->getKeyAttribute()->getCode(), Operators::EQUALS, $keyValue->getData());
        } catch (\Throwable $e) {
            try {
                $pqb->addFilter($rule->getKeyAttribute()->getCode(), Operators::IN_LIST, [$keyValue->getData()]);
            } catch (\Throwable $e) {
                throw new \Exception('Unsupported attribute type used as key attribute in a rule: ' . $rule->getKeyAttribute()->getCode());
            }
        }
        $pqb->addFilter('family', Operators::IN_LIST, [$rule->getDestinationFamily()->getCode()]);

        $destinationProducts = $pqb->execute();
        foreach ($destinationProducts as $destinationProduct) {
            $this->copy($sourceProduct, $destinationProduct, $attributes, $stepExecution);
        }

        if (0 === count($destinationProducts) && 0 === count($rule->getDestinationFamily()->getFamilyVariants())) {
            $this->createNewDestinationProduct($sourceProduct, $rule, $attributes, $stepExecution);

            foreach ($this->productsToSave as $product) {
                $this->productSaver->save($product);
            }

            $stepExecution->incrementSummaryInfo('destination_products_created', 1);
        } else {
            foreach ($this->productsToSave as $id => $product) {
                $this->productSaver->save($product);
                if (empty($this->destinationProductsId[$id])) {
                    $stepExecution->incrementSummaryInfo('destination_products_found_and_saved', 1);
                    $this->destinationProductsId[$id] = $id;
                }
            }
            foreach ($this->productModelsToSave as $id => $productModel) {
                $this->productModelSaver->save($productModel);
                if (empty($this->destinationProductModelsId[$id])) {
                    $stepExecution->incrementSummaryInfo('destination_product_models_found_and_saved', 1);
                    $this->destinationProductModelsId[$id] = $id;
                }
            }
        }
    }

    private function copy(
        EntityWithValuesInterface $sourceProduct,
        EntityWithValuesInterface $destinationProduct,
        array $attributes,
        StepExecution $stepExecution
    ): void {
        try {
            $result = $this->ruleProcessorCopier->copy($sourceProduct, $destinationProduct, $attributes);
            if ($result) {
                $this->addEntityToSave($destinationProduct);
            }

            if ($destinationProduct instanceof ProductModelInterface) {
                foreach ($destinationProduct->getProductModels() as $productModel) {
                    $this->copy($sourceProduct, $productModel, $attributes, $stepExecution);
                }
                foreach ($destinationProduct->getProducts() as $product) {
                    $this->copy($sourceProduct, $product, $attributes, $stepExecution);
                }
            }
        } catch (\Throwable $e) {
            $msg = sprintf(
                'Problem with copying data from product %s to product %s. Error: %s',
                $sourceProduct->getId(),
                $destinationProduct->getId(),
                $e->getMessage()
            );
            $invalidItem = new SimpleInvalidItem(
                [
                    'sourceProduct'      => $sourceProduct->getId(),
                    'destinationProduct' => $destinationProduct->getId(),
                ]
            );
            $stepExecution->addWarning($msg, [], $invalidItem);
        }
    }

    private function addEntityToSave(EntityWithValuesInterface $entity): void
    {
        if ($entity instanceof ProductInterface) {
            $this->productsToSave[$entity->getId()] = $entity;
        } elseif ($entity instanceof ProductModelInterface) {
            $this->productModelsToSave[$entity->getId()] = $entity;
        }
    }

    private function createNewDestinationProduct(EntityWithValuesInterface $sourceEntity, Rule $rule, array $attributes, StepExecution $stepExecution): void
    {
        $destinationProduct = $this->productBuilder->createProduct(
            Uuid::uuid4()->toString(),
            $rule->getDestinationFamily()->getCode()
        );

        $this->addEntityToSave($destinationProduct);

        $this->copy($sourceEntity, $destinationProduct, $attributes, $stepExecution);
    }
}