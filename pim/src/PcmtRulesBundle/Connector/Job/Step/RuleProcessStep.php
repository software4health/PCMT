<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Repository\RuleRepository;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProcessor;

class RuleProcessStep extends AbstractStep
{
    /** @var RuleAttributeProvider */
    private $attributeProvider;

    /** @var RuleRepository */
    private $ruleRepository;

    /** @var RuleProcessor */
    private $ruleProductProcessor;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    public const BATCH_SIZE = 20;

    public function setAttributeProvider(RuleAttributeProvider $attributeProvider): void
    {
        $this->attributeProvider = $attributeProvider;
    }

    public function setRuleRepository(RuleRepository $ruleRepository): void
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function setRuleProductProcessor(RuleProcessor $ruleProductProcessor): void
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    public function setPqbFactory(ProductQueryBuilderFactoryInterface $pqbFactory): void
    {
        $this->pqbFactory = $pqbFactory;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $ruleId = $stepExecution->getJobParameters()->get('ruleId');

        $stepExecution->addSummaryInfo('rule_id', $ruleId);

        /** @var Rule $rule */
        $rule = $this->ruleRepository->find($ruleId);
        $attributes = $this->attributeProvider->getAllForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());

        $stepExecution->addSummaryInfo('attributes_found', count($attributes));
        $stepExecution->addSummaryInfo('source_products_found', 0);
        $stepExecution->addSummaryInfo('source_product_models_found', 0);
        $stepExecution->addSummaryInfo('source_products_processed', 0);
        $stepExecution->addSummaryInfo('source_product_models_processed', 0);
        $stepExecution->addSummaryInfo('destination_products_found_and_saved', 0);
        $stepExecution->addSummaryInfo('destination_product_models_found_and_saved', 0);
        $stepExecution->addSummaryInfo('destination_products_created', 0);

        $result = true;
        $offset = 0;
        while ($result) {
            $result = $this->processBatch($stepExecution, $rule, $offset);
            $offset += self::BATCH_SIZE;
        }
    }

    private function processBatch(StepExecution $stepExecution, Rule $rule, int $offset): bool
    {
        $count = 0;
        // look in ElasticSearch index
        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
            'limit'          => self::BATCH_SIZE,
            'from'           => $offset,
        ]);
        $pqb->addFilter('family', Operators::IN_LIST, [$rule->getSourceFamily()->getCode()]);

        $entityCursor = $pqb->execute();

        foreach ($entityCursor as $entity) {
            $count++;
            if ($entity instanceof ProductModelInterface) {
                $stepExecution->incrementSummaryInfo('source_product_models_found', 1);
                $this->processProductModel($stepExecution, $rule, $entity);
            } else {
                $stepExecution->incrementSummaryInfo('source_products_found', 1);
                $this->processProduct($stepExecution, $rule, $entity);
            }
        }

        return $count ? true : false;
    }

    private function processProductModel(StepExecution $stepExecution, Rule $rule, ProductModelInterface $productModel): void
    {
        $stepExecution->incrementSummaryInfo('source_product_models_processed', 1);
        $this->ruleProductProcessor->process($stepExecution, $rule, $productModel);
        foreach ($productModel->getProductModels() as $subProductModel) {
            $this->processProductModel($stepExecution, $rule, $subProductModel);
        }
        foreach ($productModel->getProducts() as $productVariant) {
            $this->processProduct($stepExecution, $rule, $productVariant);
        }
    }

    private function processProduct(StepExecution $stepExecution, Rule $rule, ProductInterface $product): void
    {
        $stepExecution->incrementSummaryInfo('source_products_processed', 1);
        $this->ruleProductProcessor->process($stepExecution, $rule, $product);
    }
}