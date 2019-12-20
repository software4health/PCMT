<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    /** @var string */
    protected static $defaultName = 'pcmt:command';

    public function configure(): void
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $path1 = 'src/PcmtCustomDatasetBundle/Resources/fixtures/pcmt_global/import_files/5_families.xlsx';
        $path2 = 'src/PcmtCustomDatasetBundle/Resources/fixtures/pcmt_global/mapping/E2OpenMapping.xlsx';
        $xlsReader = new XlsxReader();
        $readFile = $xlsReader->load($path1);
        $writeFile = $xlsReader->load($path2);
        $workSheet = $readFile->getActiveSheet();
        $saveSheet = $writeFile->getActiveSheet();
        $xlsWriter = new XlsxWriter($writeFile);

        $cellValue = $workSheet->getCellByColumnAndRow(3, 7)
            ->getValue()
            ->__toString();

        $arrayValues = explode(',', $cellValue);

        array_walk(
            $arrayValues,
            function ($element, &$key) use ($saveSheet): void {
                $cell = 'B' . (string) ($key + 2);
                $saveSheet->setCellValue($cell, $element);
            }
        );
        $xlsWriter->save($path2);
    }
}