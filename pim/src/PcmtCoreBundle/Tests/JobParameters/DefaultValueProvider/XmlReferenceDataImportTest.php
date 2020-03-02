<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\JobParameters\DefaultValueProvider;

use PcmtCoreBundle\JobParameters\DefaultValueProvider\XmlReferenceDataImport;
use PHPUnit\Framework\TestCase;

class XmlReferenceDataImportTest extends TestCase
{
    public function testGetDefaultValues(): void
    {
        $o = new XmlReferenceDataImport([]);
        $values = $o->getDefaultValues();
        $this->assertArrayHasKey('xmlMapping', $values);
        $this->assertArrayHasKey('uploadAllowed', $values);
        $this->assertArrayHasKey('decimalSeparator', $values);
        $this->assertArrayHasKey('dirPath', $values);
        $this->assertIsString($values['dirPath']);
        $this->assertNull($values['filePath']);
    }
}