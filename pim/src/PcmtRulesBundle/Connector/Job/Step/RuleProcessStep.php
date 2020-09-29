<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Repository\RuleRepository;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Service\RuleProductProcessor;

class RuleProcessStep extends AbstractStep
{
    /** @var RuleAttributeProvider */
    private $attributeProvider;

    /** @var RuleRepository */
    private $ruleRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RuleProductProcessor */
    private $ruleProductProcessor;

    public const BATCH_SIZE = 100;

    public function setAttributeProvider(RuleAttributeProvider $attributeProvider): void
    {
        $this->attributeProvider = $attributeProvider;
    }

    public function setRuleRepository(RuleRepository $ruleRepository): void
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function setProductRepository(ProductRepositoryInterface $productRepository): void
    {
        $this->productRepository = $productRepository;
    }

    public function setRuleProductProcessor(RuleProductProcessor $ruleProductProcessor): void
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $ruleId = $stepExecution->getJobParameters()->get('ruleId');

        $stepExecution->addSummaryInfo('rule_id', $ruleId);

        /** @var Rule $rule */
        $rule = $this->ruleRepository->find($ruleId);
        $attributes = $this->attributeProvider->getForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());

        $stepExecution->addSummaryInfo('attributes_found', count($attributes));
        $stepExecution->addSummaryInfo('source_products_found', 0);
        $stepExecution->addSummaryInfo('source_products_processed', 0);
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
        $products = $this->productRepository->findBy(['family' => $rule->getSourceFamily()], null, self::BATCH_SIZE, $offset);
        $stepExecution->incrementSummaryInfo('source_products_found', count($products));

        foreach ($products as $product) {
            $stepExecution->incrementSummaryInfo('source_products_processed', 1);
            $this->ruleProductProcessor->process($stepExecution, $rule, $product);
        }

        return $products ? true : false;
    }
}