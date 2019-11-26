<?php

declare(strict_types=1);

namespace PcmtPcmtConnectorBundle\Tests\Step;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Pcmt\PcmtConnectorBundle\Processor\PcmtReferenceDataProcessor;
use Pcmt\PcmtConnectorBundle\Reader\File\GS1ReferenceDataXmlReader;
use Pcmt\PcmtConnectorBundle\Reader\File\ReferenceDataXmlReader;
use Pcmt\PcmtConnectorBundle\Writer\ReferenceDataWriter;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ImportRefDataFilesStepTest extends TestCase
{
    /** @var ReferenceDataXmlReader|Mock */
    protected $readerMock;

    /** @var PcmtReferenceDataProcessor|Mock */
    protected $processorMock;

    /** @var ReferenceDataWriter|Mock */
    protected $writerMock;

    /** @var JobRepositoryInterface|Mock */
    protected $jobRepositoryMock;

    /** @var EventDispatcher|Mock */
    protected $eventDispatcherMock;

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(GS1ReferenceDataXmlReader::class);
        $this->processorMock = $this->createMock(PcmtReferenceDataProcessor::class);
        $this->writerMock = $this->createMock(ReferenceDataWriter::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $this->jobRepositoryMock = $this->createMock(DoctrineJobRepository::class);
        parent::setUp();
    }

    public function testWillExecuteTaskConsecutively(): void
    {
        $step = $this->getItemStepInstance();
        $stepName = 'import_xml_data_files';
        $jobExecution = $this->createMock(JobExecution::class);

        $this->readerMock->expects($this->at(0))
            ->method('read');

        $this->processorMock->expects($this->at(0))
            ->method('process');

        $this->writerMock->expects($this->at(0))
            ->method('write');

        $step->doExecute(new StepExecution($stepName, $jobExecution));
    }

    protected function getItemStepInstance(): ItemStep
    {
        return new ItemStep(
            'process',
            $this->eventDispatcherMock,
            $this->jobRepositoryMock,
            $this->readerMock,
            $this->processorMock,
            $this->writerMock
        );
    }
}