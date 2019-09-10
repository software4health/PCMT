<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Step;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessRefData extends ItemStep
{
    protected $referenceDataReader;

    protected $referenceDataRepository;

    protected $referenceDataCreator;

    protected $refereneDataUpdater;

    public function __construct($name, EventDispatcherInterface $eventDispatcher, JobRepositoryInterface $jobRepository, ItemReaderInterface $reader, ItemProcessorInterface $processor, ItemWriterInterface $writer, $batchSize = 100)
    {
        parent::__construct($name, $eventDispatcher, $jobRepository, $reader, $processor, $writer, $batchSize);
    }
}