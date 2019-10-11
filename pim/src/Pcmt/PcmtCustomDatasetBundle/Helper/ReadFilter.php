<?php
declare(strict_types=1);

namespace Pcmt\PcmtCustomDatasetBundle\Helper;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;


class ReadFilter implements IReadFilter
{
  private $columnToFilter;

  public function __construct(string $columnToFilter = "ColumnToFilter")
  {
    $this->columnToFilter = $columnToFilter;
  }

  public function readCell($column, $row, $worksheetName = ''): bool
  {
    if ($row == 1 || $column !== $this->columnToFilter) {
      return true;
    }
    return false;
  }
}