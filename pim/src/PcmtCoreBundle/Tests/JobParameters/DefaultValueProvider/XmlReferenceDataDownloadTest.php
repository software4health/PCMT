<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\JobParameters\DefaultValueProvider;

use PcmtCoreBundle\JobParameters\DefaultValueProvider\XmlReferenceDataDownload;
use PHPUnit\Framework\TestCase;

class XmlReferenceDataDownloadTest extends TestCase
{
    /**
     * @dataProvider dataGetDefaultValues
     */
    public function testGetDefaultValues(string $dirName): void
    {
        $o = new XmlReferenceDataDownload([], $dirName);
        $values = $o->getDefaultValues();
        $this->assertArrayHasKey('xml_data_pick_urls', $values);
        $this->assertIsArray($values['xml_data_pick_urls']);
        $this->assertArrayHasKey('dirPath', $values);
        $this->assertIsString($values['dirPath']);
        $this->assertStringContainsString($dirName, $values['dirPath']);
        $this->assertNull($values['filePath']);
    }

    public function dataGetDefaultValues(): array
    {
        return [
            ['xxxxxx'],
            ['ref_data'],
        ];
    }
}