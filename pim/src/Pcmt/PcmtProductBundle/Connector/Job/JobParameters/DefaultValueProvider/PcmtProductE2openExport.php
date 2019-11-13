<?php

namespace Pcmt\PcmtProductBundle\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductXlsxExport;


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
