<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx\ProductWriter;

class MstSupplierExportWriter extends ProductWriter implements CrossJoinExportWriterInterface
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

    public function writeCross(array $items, array $crossItems): void
    {
        $result = [];
        foreach ($items as $item) {
            foreach ($crossItems as $crossItem) {
                unset($crossItem['values']['sku']);
                $result[] = [
                    'identifier' => $item['identifier'],
                    'values'     => array_merge_recursive($item['values'], $crossItem['values']),
                ];
            }
        }
        $this->write($result);
    }
}
