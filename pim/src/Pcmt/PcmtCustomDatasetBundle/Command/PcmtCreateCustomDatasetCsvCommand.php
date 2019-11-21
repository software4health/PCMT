<?php
declare(strict_types=1);

namespace Pcmt\PcmtCustomDatasetBundle\Command;

use Pcmt\PcmtCustomDatasetBundle\Helper\ReadFilter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

/**
 * Command that we can use in terminal to create csv files from xlsx files
 * xlsx files path: Resources/fixtures/pcmt_global/import_files/
 * csv files path: Resources/fixtures/pcmt_global/import_files/csv/
 */
class PcmtCreateCustomDatasetCsvCommand extends ContainerAwareCommand
{
  /**
   * run inside terminal in fpm docker: bin/console $defaultName
   */

  /** @var string */
  protected static $defaultName = 'pcmt:custom-dataset:csv:create';

  /** @var string */
  protected $filesFolderPath;

  protected function execute(InputInterface $input, OutputInterface $output): void
  {
    $bundleDir = __DIR__.'/../';
    $this->filesFolderPath = $bundleDir."Resources/fixtures/pcmt_global/import_files/";
    $fileNameList = $this->getFileNameList();
    $bar = new ProgressBar($output, count($fileNameList)+1);
    $bar->setFormat("verbose");
    $bar->start();
    foreach($fileNameList as $fileName) {
      $output->writeln("\nnow: ".$fileName."...");
      $this->convertXlsxToCsv($fileName);
      $bar->advance();
    }
    $this->createAttributeGroupsWithoutAttributes($output);
    $bar->finish();
    $output->writeln("");
    $output->writeln("All csv data created");
  }

  /**
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
   * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
   */
  protected function createAttributeGroupsWithoutAttributes(OutputInterface $output): void
  {
    $fileName = "2_attribute_groups";
    $columnToFilter = "ColumnToFilter";
    $reader = new XlsxReader();
    $spreadsheet = $reader->load($this->filesFolderPath.$fileName.'.xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumn++;
    for ($col = 'A'; $col != $highestColumn; ++$col) {
      $value = $worksheet->getCell($col . "1")->getFormattedValue();
      if ($value === "attributes") {
        $columnToFilter = $col;
        break;
      }
    }
    $filterSubset = new ReadFilter($columnToFilter);
    $output->writeln([
      "",
      "--------------------------------",
      "Column to filter in 2_attribute_groups.xlsx: ".$columnToFilter,
      "--------------------------------",
    ]);
    $reader->setReadFilter($filterSubset);
    $spreadsheet = $reader->load($this->filesFolderPath.$fileName.'.xlsx');
    $csv_writer = new CsvWriter($spreadsheet);
    $csv_writer->setDelimiter(";");
    $csv_writer->save($this->filesFolderPath."csv/".$fileName.".csv");
  }

  private function convertXlsxToCsv(string $fileName): void
  {
    $reader = new XlsxReader();
    $spreadsheet = $reader->load($this->filesFolderPath.$fileName.".xlsx");
    $worksheet = $spreadsheet->getActiveSheet();
    $csv_writer = new CsvWriter($spreadsheet);
    $csv_writer->setDelimiter(";");
    $csv_writer->save($this->filesFolderPath."csv/".$fileName.".csv");
  }

  private function getFileNameList(): array
  {
    return [
      "1_categories",
      "3_attributes",
      "4_attribute_options",
      "5_families",
      "6_family_variants",
      "7_product_models",
      "8_products",
      "8_products_gs1",
      "9_masterdata_entries",
    ];
  }
}