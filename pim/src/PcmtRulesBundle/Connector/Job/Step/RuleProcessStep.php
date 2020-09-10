<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PcmtRulesBundle\Entity\Rule;
use PcmtRulesBundle\Repository\RuleRepository;
use PcmtRulesBundle\Service\RuleAttributeProvider;

class RuleProcessStep extends AbstractStep
{
    /** @var RuleAttributeProvider */
    private $attributeProvider;

    /** @var RuleRepository */
    private $ruleRepository;

    public function setAttributeProvider(RuleAttributeProvider $attributeProvider): void
    {
        $this->attributeProvider = $attributeProvider;
    }

    public function setRuleRepository(RuleRepository $ruleRepository): void
    {
        $this->ruleRepository = $ruleRepository;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $ruleId = $stepExecution->getJobParameters()->get('ruleId');

        $stepExecution->addSummaryInfo('rule_id', $ruleId);

        /** @var Rule $rule */
        $rule = $this->ruleRepository->find($ruleId);
        $attributes = $this->attributeProvider->getForFamilies($rule->getSourceFamily(), $rule->getDestinationFamily());

        $stepExecution->addSummaryInfo('attributes_found', count($attributes));

        // to be continued...
    }
}