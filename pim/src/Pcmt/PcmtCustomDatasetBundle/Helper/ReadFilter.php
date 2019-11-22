<?php

declare(strict_types=1);

namespace Pcmt\PcmtCustomDatasetBundle\Helper;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ReadFilter implements IReadFilter
{
    /** @var string */
    private $columnToFilter;

    public function __construct(string $columnToFilter = 'ColumnToFilter')
    {
        $this->columnToFilter = $columnToFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function readCell($column, $row, $worksheetName = ''): bool
    {
        if (1 === $row || $column !== $this->columnToFilter) {
            return true;
        }

        return false;
    }
}