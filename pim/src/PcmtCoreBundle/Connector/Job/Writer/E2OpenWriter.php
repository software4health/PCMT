<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer;

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

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'identifier' => $item['identifier'],
                'values'     => $item['values'],
            ];
        }

        parent::write($result);
    }
}
