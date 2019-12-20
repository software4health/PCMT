<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * To run, type:
 * phpunit src/PcmtCoreBundle/Tests/
 * in docker console
 */
class DraftStatusTranslatorServiceTest extends TestCase
{
    /**
     * @var DraftStatusTranslatorService
     */
    private $draftStatusTranslatorService;

    /**
     * @var string
     */
    private $nameTranslated = 'name translated';

    protected function setUp(): void
    {
        $translatorService = $this->createMock(TranslatorInterface::class);
        $translatorService->method('trans')->willReturn($this->nameTranslated);
        $this->draftStatusTranslatorService = new DraftStatusTranslatorService($translatorService);
        parent::setUp();
    }

    public function testGetName(): void
    {
        $name = $this->draftStatusTranslatorService->getName(AbstractDraft::STATUS_NEW);
        $this->assertNotEmpty($name);
        $this->assertIsString($name);
    }

    public function testGetNameTranslated(): void
    {
        $name = $this->draftStatusTranslatorService->getNameTranslated(AbstractDraft::STATUS_NEW);
        $this->assertSame($this->nameTranslated, $name);
    }
}