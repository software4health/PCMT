<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PcmtRulesBundle\Service\CopyProductsRule\CopyProductsRuleProcessor;

class CopyProductsRuleStep extends AbstractStep
{
    public const PARAM_SOURCE_FAMILY = 'sourceFamily';

    public const PARAM_DESTINATION_FAMILY = 'destinationFamily';

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var CopyProductsRuleProcessor */
    private $productProcessor;

    public function setFamilyRepository(FamilyRepositoryInterface $familyRepository): void
    {
        $this->familyRepository = $familyRepository;
    }

    public function setPqbFactory(ProductQueryBuilderFactoryInterface $pqbFactory): void
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function setProductProcessor(CopyProductsRuleProcessor $productProcessor): void
    {
        $this->productProcessor = $productProcessor;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $jobParameters = $stepExecution->getJobParameters();

        $text = [];
        foreach ($jobParameters->all() as $key => $value) {
            $text[] = $key.' : '. $value;
        }
        $stepExecution->addSummaryInfo('parameters', implode(', ', $text));

        $sourceFamilyCode = $jobParameters->get(self::PARAM_SOURCE_FAMILY);
        $destinationFamilyCode = $jobParameters->get(self::PARAM_DESTINATION_FAMILY);

        $sourceFamily = $this->familyRepository->findOneBy(['code' => $sourceFamilyCode]);
        $destinationFamily = $this->familyRepository->findOneBy(['code' => $destinationFamilyCode]);

        if (!$sourceFamily || !$destinationFamily) {
            return;
        }

        $stepExecution->addSummaryInfo('sf', $sourceFamily->getCode());
        $stepExecution->addSummaryInfo('df', $destinationFamily->getCode());

        $pqb = $this->pqbFactory->create([
            'default_locale' => null,
            'default_scope'  => null,
            'limit'          => 100000,
        ]);
        $pqb->addFilter('family', Operators::IN_LIST, [$sourceFamily->getCode()]);
        $entityCursor = $pqb->execute();

        foreach ($entityCursor as $product) {
            $stepExecution->incrementSummaryInfo('source_products_found', 1);
            $this->productProcessor->process($stepExecution, $destinationFamily, $product);
        }
    }
}