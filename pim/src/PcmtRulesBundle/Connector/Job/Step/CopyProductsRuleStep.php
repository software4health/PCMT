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

class CopyProductsRuleStep extends AbstractStep
{
    public const PARAM_SOURCE_FAMILY = 'sourceFamily';

    public const PARAM_DESTINATION_FAMILY = 'destinationFamily';

    protected function doExecute(StepExecution $stepExecution): void
    {
        $jobParameters = $stepExecution->getJobParameters();

        $text = [];
        foreach ($jobParameters->all() as $key => $value) {
            $text[] = $key.' : '. $value;
        }
        $stepExecution->addSummaryInfo('parameters', implode(', ', $text));
    }
}