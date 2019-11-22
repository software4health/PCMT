<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Service;

use Pcmt\PcmtProductBundle\Service\DraftStatusListService;
use PHPUnit\Framework\TestCase;

/**
 * To run, type:
 * phpunit src/Pcmt/PcmtProductBundle/Tests/
 * in docker console
 */
class DraftStatusListServiceTest extends TestCase
{
    /**
     * @var DraftStatusListService
     */
    private $draftStatusListService;

    public function setUp(): void
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