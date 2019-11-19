<?php

namespace Pcmt\PcmtProductBundle\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class HashFirstCellStep extends AbstractStep
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var string DateTime format for the file path placeholder */
    protected $datetimeFormat = 'Y-m-d_H-i-s';


    protected function doExecute(StepExecution $stepExecution): void
    {
        $this->setStepExecution($stepExecution);
        $cell = "A1";
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($this->getPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $value = $worksheet->getCell($cell)->getFormattedValue();
        $worksheet->setCellValue($cell, "#" . $value);
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($this->getPath());
    }


    private function getPath(): string
    {
        $parameters = $this->stepExecution->getJobParameters();
        $filePath = $parameters->get('filePath');
        if (false !== strpos($filePath, '%')) {
            $jobExecution = $this->stepExecution->getJobExecution();
            $datetime = $jobExecution->getStartTime()->format($this->datetimeFormat);
            $defaultPlaceholders = [
                '%datetime%'  => $datetime,
                '%job_label%' => '',
            ];
            if (null !== $jobExecution->getJobInstance()) {
                $defaultPlaceholders['%job_label%'] = $this->sanitize($jobExecution->getJobInstance()->getLabel());
            }
            $filePath = strtr($filePath, $defaultPlaceholders);
        }
        return $filePath;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function sanitize($value): string
    {
        return preg_replace('#[^A-Za-z0-9\.]#', '_', $value);
    }
}