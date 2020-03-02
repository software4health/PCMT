<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;

trait SupportedJobsTrait
{
    /** @var string[] */
    protected $supportedJobNames = [];

    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}