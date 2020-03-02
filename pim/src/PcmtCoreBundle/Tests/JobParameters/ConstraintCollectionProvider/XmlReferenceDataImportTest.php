<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\JobParameters\ConstraintCollectionProvider;

use PcmtCoreBundle\JobParameters\ConstraintCollectionProvider\XmlReferenceDataImport;
use PHPUnit\Framework\TestCase;

class XmlReferenceDataImportTest extends TestCase
{
    public function testGetConstraintCollection(): void
    {
        $o = new XmlReferenceDataImport([]);
        $collection = $o->getConstraintCollection();
        $this->assertArrayHasKey('filePath', $collection->fields);
    }
}