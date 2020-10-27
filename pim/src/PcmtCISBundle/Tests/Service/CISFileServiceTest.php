<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Service;

use PcmtCISBundle\Service\CISFileService;
use PcmtCISBundle\Service\CISFileUniqueIdentifierGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class CISFileServiceTest extends TestCase
{
    /** @var Filesystem|MockObject */
    private $filesystemMock;

    /** @var CISFileUniqueIdentifierGenerator|MockObject */
    private $uniqueIdentifierGeneratorMock;

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->uniqueIdentifierGeneratorMock = $this->createMock(CISFileUniqueIdentifierGenerator::class);
    }

    public function testCreateFile(): void
    {
        $source = 'RHSC';
        $destination = 'GS1Engine';
        $messageType = 'GDSNSubscription';
        $version = '1.0';
        $uniqueIdentifier = '2020-10-26T10:01:55+00:00';
        $path = '/path/';

        $service = new CISFileService(
            $this->filesystemMock,
            $this->uniqueIdentifierGeneratorMock,
            $path,
            $source
        );

        $this->uniqueIdentifierGeneratorMock
            ->method('generate')
            ->willReturn($uniqueIdentifier);

        $filename = "{$path}work/{$source}_{$destination}_{$messageType}_{$version}_{$uniqueIdentifier}.txt";

        $this->filesystemMock
            ->expects($this->once())
            ->method('touch')
            ->with($filename);

        $service->createFile();
    }
}