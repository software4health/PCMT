<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductXlsxExport;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class PcmtProductE2openExport extends ProductXlsxExport implements DefaultValuesProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        $parameters = parent::getDefaultValues();
        $parameters['filters']['data'] = [];

        return $parameters;
    }
}
