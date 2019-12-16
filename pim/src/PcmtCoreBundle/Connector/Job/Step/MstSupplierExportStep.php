<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Step;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use PcmtCoreBundle\Connector\Job\Reader\CrossJoinExportReaderInterface;
use PcmtCoreBundle\Connector\Job\Writer\CrossJoinExportWriterInterface;

class MstSupplierExportStep extends ItemStep
{
    /** @var CrossJoinExportWriterInterface */
    protected $writer = null;

    /** @var CrossJoinExportReaderInterface */
    protected $reader = null;

    /** @var ProductInterface[] */
    private $productsFromFamilyToCrossJoin = [];

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution): void
    {
        $this->initializeStepElements($stepExecution);
        $this->productsFromFamilyToCrossJoin = $this->getCrossProducts();
        parent::doExecute($stepExecution);
    }

    /**
     * @return ProductInterface[]
     */
    private function getCrossProducts(): array
    {
        $this->reader->setFamilyToCrossRead('MD_HUB');
        $itemsToWrite = [];
        while (true) {
            try {
                $readItem = $this->reader->readCross();
                if (null === $readItem) {
                    break;
                }
            } catch (InvalidItemException $e) {
                $this->handleStepExecutionWarning($this->stepExecution, $this->reader, $e);
                continue;
            }
            $processedItem = $this->process($readItem);
            if (null !== $processedItem) {
                $itemsToWrite[] = $processedItem;
            }
        }

        return $itemsToWrite;
    }

    /**
     * {@inheritdoc}
     */
    protected function write($processedItems): void
    {
        try {
            $this->writer->writeCross($processedItems, $this->productsFromFamilyToCrossJoin);
        } catch (InvalidItemException $e) {
            $this->handleStepExecutionWarning($this->stepExecution, $this->writer, $e);
        }
    }
}