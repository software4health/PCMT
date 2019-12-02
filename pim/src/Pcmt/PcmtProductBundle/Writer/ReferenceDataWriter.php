<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

class ReferenceDataWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $saver;

    public function __construct(BulkSaverInterface $bulkSaver)
    {
        $this->saver = $bulkSaver;
    }

    public function write(array $items): void
    {
        foreach ($items as $item) {
            $this->incrementCount($item);
        }
        $this->saveAll($items);
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function initialize(): void
    {
    }

    protected function saveAll(array $items): void
    {
        $this->saver->saveAll($items);
    }

    protected function incrementCount(ReferenceDataInterface $item): void
    {
        if ($item->getId()) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}