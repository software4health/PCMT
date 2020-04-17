<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Connector\Job\Reader;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtCoreBundle\Connector\Job\Reader\File\ReferenceDataXmlReader;
use PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReferenceDataXmlReaderTest extends TestCase
{
    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    /** @var JobParameters|MockObject */
    private $jobParametersMock;

    /** @var FileGetContentsWrapper|MockObject */
    private $fileGetContentsWrapperMock;

    /** @var ReferenceDataXmlReader */
    private $referenceDataXmlReader;

    protected function setUp(): void
    {
        $this->fileGetContentsWrapperMock = $this->createMock(FileGetContentsWrapper::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
        $this->jobParametersMock = $this->createMock(JobParameters::class);
        $this->referenceDataXmlReader = new ReferenceDataXmlReader($this->fileGetContentsWrapperMock);
        $this->referenceDataXmlReader->setStepExecution($this->stepExecutionMock);
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
        $this->stepExecutionMock->expects($this->exactly(2))
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->at(0))
            ->method('get')
            ->with('filePath')
            ->willReturn($filePath);

        $this->jobParametersMock->expects($this->at(1))
            ->method('get')
            ->with('xmlMapping')
            ->willReturn(null);

        $this->expectException(\Throwable::class);
        $this->referenceDataXmlReader->read();
    }

    public function testReaderImplementReferenceDataXmlReaderInterface(): void
    {
        $this->referenceDataXmlReader->setFilePath('file/path.xml');
        $this->stepExecutionMock->expects($this->exactly(1))
            ->method('getJobParameters')
            ->willReturn($this->jobParametersMock);

        $this->jobParametersMock->expects($this->at(0))
            ->method('get')
            ->with('xmlMapping')
            ->willReturn(null);

        $this->expectException(\Throwable::class);
        $this->referenceDataXmlReader->read();
    }
}