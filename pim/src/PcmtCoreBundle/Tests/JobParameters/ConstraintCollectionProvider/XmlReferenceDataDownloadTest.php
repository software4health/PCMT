<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PcmtCoreBundle\JobParameters\ConstraintCollectionProvider\XmlReferenceDataDownload;
use PHPUnit\Framework\TestCase;

class XmlReferenceDataDownloadTest extends TestCase
{
    /**
     * @dataProvider dataSupports
     */
    public function testSupports(string $jobName): void
    {
        $o = new XmlReferenceDataDownload([$jobName]);

        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn($jobName);
        $this->assertTrue($o->supports($job));

        $job = $this->createMock(JobInterface::class);
        $job->method('getName')->willReturn($jobName.'yyy');
        $this->assertFalse($o->supports($job));
    }

    public function dataSupports(): array
    {
        return [
            ['xxx'],
            ['cccccc_2323'],
        ];
    }

    public function testGetConstraintCollection(): void
    {
        $o = new XmlReferenceDataDownload([]);
        $collection = $o->getConstraintCollection();
        $this->assertArrayHasKey('filePath', $collection->fields);
    }
}