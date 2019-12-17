<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Reader;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtCoreBundle\Reader\File\GS1ReferenceDataXmlReader;
use PcmtCoreBundle\Reader\File\ReferenceDataXmlReader;
use PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Sabre\Xml\Service;

class TestReferenceDataXmlReader extends TestCase
{
    /** @var Service|Mock */
    protected $xmlParserMock;

    /** @var StepExecution|Mock */
    protected $stepExecutionMock;

    /** @var \PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper|Mock */
    protected $fileGetContentsWrapperMock;

    protected function setUp(): void
    {
        $this->xmlParserMock = $this->createMock(Service::class);
        $this->fileGetContentsWrapperMock = $this->createMock(FileGetContentsWrapper::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
    }

    public function inputFilePathDataProvider(): array
    {
        return [
            ['testDirectory/file01.xml'],   //proper path and file format
            ['testDirectory/file02.xml'],
            ['testDirectory/file03.xml'],
        ];
    }

    /**
     * @dataProvider inputFilePathDataProvider
     */
    public function testReadCorrectFile(string $filePath): void
    {
        $reader = $this->getReferenceDataXmlReaderInstance();
        $reader->setStepExecution($this->stepExecutionMock);
        $jobParametersMock = $this->createMock(JobParameters::class);
        $input = 'test_file_input_stream';

        $this->stepExecutionMock->expects($this->exactly(2))
            ->method('getJobParameters')
            ->willReturn($jobParametersMock);

        $jobParametersMock->expects($this->at(0))
            ->method('get')
            ->willReturn($filePath);

        $jobParametersMock->expects($this->at(1))
            ->method('get')
            ->willReturn(null);

        $this->fileGetContentsWrapperMock->expects($this->once())
            ->method('fileGetContents')
            ->willReturn($input);

        $this->xmlParserMock->expects($this->any())
            ->method('parse')
            ->with($input)
            ->willReturn([]);

        $reader->flush();
        $reader->read();
    }

    public function invalidInputFilePathDataProvider(): array
    {
        return [
            ['testDirectory/file01.yml'],
            ['testDirectory/file.docx'],
            ['testDirectory/file.cpp'],
        ];
    }

    /**
     * @dataProvider invalidInputFilePathDataProvider
     */
    public function testReadNullOrCorruptedOrWrongFileFormat(string $filePath): void
    {
        $reader = $this->getReferenceDataXmlReaderInstance();
        $reader->setStepExecution($this->stepExecutionMock);
        $jobParametersMock = $this->createMock(JobParameters::class);

        $this->stepExecutionMock->expects($this->exactly(2))
            ->method('getJobParameters')
            ->willReturn($jobParametersMock);

        $jobParametersMock->expects($this->at(0))
            ->method('get')
            ->with('filePath')
            ->willReturn($filePath);

        $jobParametersMock->expects($this->at(1))
            ->method('get')
            ->with('xmlMapping')
            ->willReturn(null);

        $this->expectException(\Throwable::class);
        $reader->read();
    }

    private function getReferenceDataXmlReaderInstance(): ReferenceDataXmlReader
    {
        return new GS1ReferenceDataXmlReader($this->fileGetContentsWrapperMock);
    }
}