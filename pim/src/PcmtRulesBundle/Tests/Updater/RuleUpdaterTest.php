<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Updater;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PcmtRulesBundle\Updater\RuleUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleUpdaterTest extends TestCase
{
    /** @var FamilyRepositoryInterface|MockObject */
    private $familyRepositoryMock;

    protected function setUp(): void
    {
        $this->familyRepositoryMock = $this->createMock(FamilyRepositoryInterface::class);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(string $uniqueId, string $sourceFamilyCode, string $destinationFamilyCode): void
    {
        $sourceFamily = (new FamilyBuilder())->withCode($sourceFamilyCode)->build();
        $destinationFamily = (new FamilyBuilder())->withCode($destinationFamilyCode)->build();
        $this->familyRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneByIdentifier')
            ->withConsecutive([$sourceFamilyCode], [$destinationFamilyCode])
            ->willReturnOnConsecutiveCalls($sourceFamily, $destinationFamily);

        $rule = (new RuleBuilder())->build();
        $data = [
            'uniqueId'          => $uniqueId,
            'sourceFamily'      => $sourceFamilyCode,
            'destinationFamily' => $destinationFamilyCode,
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);

        $this->assertSame($uniqueId, $rule->getUniqueId());
        $this->assertSame($sourceFamilyCode, $rule->getSourceFamily()->getCode());
        $this->assertSame($destinationFamilyCode, $rule->getDestinationFamily()->getCode());
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateWrongSourceFamily(string $uniqueId, string $sourceFamily, string $destinationFamily): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->familyRepositoryMock->expects($this->exactly(1))->method('findOneByIdentifier')->willReturn(null);
        $rule = (new RuleBuilder())->build();
        $data = [
            'uniqueId'          => $uniqueId,
            'sourceFamily'      => $sourceFamily,
            'destinationFamily' => $destinationFamily,
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateWrongDestinationFamily(string $uniqueId, string $sourceFamilyCode, string $destinationFamilyCode): void
    {
        $this->expectException(InvalidPropertyException::class);

        $sourceFamily = (new FamilyBuilder())->withCode($sourceFamilyCode)->build();
        $this->familyRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneByIdentifier')
            ->withConsecutive([$sourceFamilyCode], [$destinationFamilyCode])
            ->willReturnOnConsecutiveCalls($sourceFamily, null);

        $rule = (new RuleBuilder())->build();
        $data = [
            'uniqueId'          => $uniqueId,
            'sourceFamily'      => $sourceFamilyCode,
            'destinationFamily' => $destinationFamilyCode,
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);
    }

    public function dataUpdate(): array
    {
        return [
            ['NEW UNIQUE ID', 'xxx', 'yyy'],
        ];
    }

    public function testUpdateWrongObject(): void
    {
        $this->expectException(InvalidObjectException::class);
        $object = (new FamilyBuilder())->build();
        $data = [];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($object, $data);
    }

    private function getRuleUpdaterInstance(): RuleUpdater
    {
        return new RuleUpdater($this->familyRepositoryMock);
    }
}