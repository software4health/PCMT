<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\Draft;

use PcmtCoreBundle\Service\Draft\DraftStatusListService;
use PHPUnit\Framework\TestCase;

/**
 * To run, type:
 * phpunit src/PcmtCoreBundle/Tests/
 * in docker console
 */
class DraftStatusListServiceTest extends TestCase
{
    /**
     * @var DraftStatusListService
     */
    private $draftStatusListService;

    protected function setUp(): void
    {
        $this->draftStatusListService = new DraftStatusListService();
        parent::setUp();
    }

    public function testGetAll(): void
    {
        $list = $this->draftStatusListService->getAll();
        $this->assertIsArray($list);
        $this->assertGreaterThan(2, count($list));
        $this->assertIsInt(reset($list));
    }
}