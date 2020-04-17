<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use DirectoryIterator;
use PcmtCoreBundle\Connector\Job\InvalidItems\XmlInvalidItem;
use PcmtCoreBundle\Connector\Job\JobParameters\DefaultValueProvider\ReferenceDataXmlImportProvider;
use PcmtCoreBundle\Connector\Job\Reader\File\ReferenceDataXmlReaderInterface;

class ReferenceDataXmlImportStep extends ItemStep
{
    /** @var ReferenceDataXmlReaderInterface */
    protected $reader = null;

    /** @var \RegexIterator */
    protected $fileIterator;

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution): void
    {
        $selectedFilePath = $stepExecution->getJobParameters()->get('filePath');
        if (ReferenceDataXmlImportProvider::ALL_FILES !== $selectedFilePath) {
            parent::doExecute($stepExecution);

            return;
        }
        $dirPath = $stepExecution->getJobParameters()->get('dirPath');
        foreach (new DirectoryIterator($dirPath) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            $currentFile = $fileInfo->getPath() . '/' . $fileInfo->getFilename();
            try {
                $this->reader->setFilePath($currentFile);
                parent::doExecute($stepExecution);
            } catch (\Throwable $exception) {
                $invalidItem = new XmlInvalidItem($currentFile);
                $stepExecution->addWarning(
                    $exception->getMessage(),
                    [],
                    $invalidItem
                );
                $stepExecution->incrementSummaryInfo('failed');
            }
            $stepExecution->incrementSummaryInfo($fileInfo->getFilename());
        }
    }
}