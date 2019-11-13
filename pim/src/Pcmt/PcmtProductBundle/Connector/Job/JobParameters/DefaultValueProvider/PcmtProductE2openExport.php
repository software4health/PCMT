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
        $channels = $this->channelRepository->getFullChannels();
        $defaultChannelCode = (0 !== count($channels)) ? $channels[0]->getCode() : null;

        $localesCodes = $this->localeRepository->getActivatedLocaleCodes();
        $defaultLocaleCodes = (0 !== count($localesCodes)) ? [$localesCodes[0]] : [];

        $parameters['filters'] = [
            'data'      => [
            ],
            'structure' => [
                'scope'   => $defaultChannelCode,
                'locales' => $defaultLocaleCodes,
            ],
        ];
        return $parameters;
    }
}
