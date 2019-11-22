<?php

namespace Pcmt\PcmtProductBundle\Connector\Job\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx\ProductWriter;

class E2OpenWriter extends ProductWriter
{
    /**
     * {@inheritdoc}
     */
    public function getPath(array $placeholders = []): string
    {
        $jobExecution = $this->stepExecution->getJobExecution();
        $placeholders = array_merge(
            $placeholders,
            ['%datetime%' => $jobExecution->getStartTime()->format($this->datetimeFormat)]
        );

        return parent::getPath($placeholders);
    }
}
