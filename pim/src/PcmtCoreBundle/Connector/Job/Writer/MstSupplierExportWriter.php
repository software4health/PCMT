<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer;

class MstSupplierExportWriter extends E2OpenWriter implements CrossJoinExportWriterInterface
{
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
