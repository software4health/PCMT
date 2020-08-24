<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use PcmtRulesBundle\Saver\RuleSaver;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleSaverTest extends TestCase
{
    /** @var ObjectManager|MockObject */
    private $objectManagerMock;

    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcherMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
    }

    public function testSave(): void
    {
        $rule = (new RuleBuilder())->build();

        $this->objectManagerMock->expects($this->once())->method('persist')->with($rule);
        $this->objectManagerMock->expects($this->once())->method('flush');
        $this->eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $saver = $this->getRuleSaverInstance();
        $saver->save($rule);
    }

    public function testSaveWrongObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $object = (new FamilyBuilder())->build();
        $saver = $this->getRuleSaverInstance();
        $saver->save($object);
    }

    private function getRuleSaverInstance(): RuleSaver
    {
        return new RuleSaver($this->objectManagerMock, $this->eventDispatcherMock);
    }
}