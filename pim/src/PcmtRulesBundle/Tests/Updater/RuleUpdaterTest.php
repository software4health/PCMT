<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Updater;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PcmtRulesBundle\Updater\RuleUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleUpdaterTest extends TestCase
{
    /** @var FamilyRepositoryInterface|MockObject */
    private $familyRepositoryMock;

    /** @var AttributeRepositoryInterface|MockObject */
    private $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->familyRepositoryMock = $this->createMock(FamilyRepositoryInterface::class);
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(string $uniqueId, string $sourceFamilyCode, string $destinationFamilyCode, string $keyAttributeCode): void
    {
        $sourceFamily = (new FamilyBuilder())->withCode($sourceFamilyCode)->build();
        $destinationFamily = (new FamilyBuilder())->withCode($destinationFamilyCode)->build();
        $keyAttribute = (new AttributeBuilder())->withCode($keyAttributeCode)->build();
        $this->familyRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneByIdentifier')
            ->withConsecutive([$sourceFamilyCode], [$destinationFamilyCode])
            ->willReturnOnConsecutiveCalls($sourceFamily, $destinationFamily);
        $this->attributeRepositoryMock
            ->expects($this->exactly(1))
            ->method('findOneByIdentifier')
            ->with($keyAttributeCode)
            ->willReturn($keyAttribute);

        $rule = (new RuleBuilder())->build();
        $data = [
            'unique_id'          => $uniqueId,
            'source_family'      => $sourceFamilyCode,
            'destination_family' => $destinationFamilyCode,
            'key_attribute'      => $keyAttributeCode,
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);

        $this->assertSame($uniqueId, $rule->getUniqueId());
        $this->assertSame($sourceFamilyCode, $rule->getSourceFamily()->getCode());
        $this->assertSame($destinationFamilyCode, $rule->getDestinationFamily()->getCode());
        $this->assertSame($keyAttributeCode, $rule->getKeyAttribute()->getCode());
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateWrongSourceFamily(string $uniqueId, string $sourceFamily, string $destinationFamily, string $keyAttributeCode): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->familyRepositoryMock->expects($this->exactly(1))->method('findOneByIdentifier')->willReturn(null);
        $rule = (new RuleBuilder())->build();
        $data = [
            'unique_id'          => $uniqueId,
            'source_family'      => $sourceFamily,
            'destination_family' => $destinationFamily,
            'key_attribute'      => $keyAttributeCode,
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateWrongDestinationFamily(string $uniqueId, string $sourceFamilyCode, string $destinationFamilyCode, string $keyAttributeCode): void
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
            'unique_id'          => $uniqueId,
            'source_family'      => $sourceFamilyCode,
            'destination_family' => $destinationFamilyCode,
            'key_attribute'      => $keyAttributeCode,
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateWrongKeyAttribute(string $uniqueId, string $sourceFamilyCode, string $destinationFamilyCode, string $keyAttributeCode): void
    {
        $this->expectException(InvalidPropertyException::class);

        $sourceFamily = (new FamilyBuilder())->withCode($sourceFamilyCode)->build();
        $destinationFamily = (new FamilyBuilder())->withCode($destinationFamilyCode)->build();
        $this->familyRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneByIdentifier')
            ->withConsecutive([$sourceFamilyCode], [$destinationFamilyCode])
            ->willReturnOnConsecutiveCalls($sourceFamily, $destinationFamily);
        $this->attributeRepositoryMock
            ->expects($this->exactly(1))
            ->method('findOneByIdentifier')
            ->with($keyAttributeCode)
            ->willReturn(null);

        $rule = (new RuleBuilder())->build();
        $data = [
            'unique_id'          => $uniqueId,
            'source_family'      => $sourceFamilyCode,
            'destination_family' => $destinationFamilyCode,
            'key_attribute'      => $keyAttributeCode,
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdateNoKeyAttribute(string $uniqueId, string $sourceFamilyCode, string $destinationFamilyCode, string $keyAttributeCode): void
    {
        $sourceFamily = (new FamilyBuilder())->withCode($sourceFamilyCode)->build();
        $destinationFamily = (new FamilyBuilder())->withCode($destinationFamilyCode)->build();
        $this->familyRepositoryMock
            ->expects($this->exactly(2))
            ->method('findOneByIdentifier')
            ->withConsecutive([$sourceFamilyCode], [$destinationFamilyCode])
            ->willReturnOnConsecutiveCalls($sourceFamily, $destinationFamily);
        $this->attributeRepositoryMock
            ->expects($this->never())
            ->method('findOneByIdentifier');

        $rule = (new RuleBuilder())->build();
        $data = [
            'unique_id'          => $uniqueId,
            'source_family'      => $sourceFamilyCode,
            'destination_family' => $destinationFamilyCode,
            'key_attribute'      => '',
        ];
        $updater = $this->getRuleUpdaterInstance();
        $updater->update($rule, $data);
    }

    public function dataUpdate(): array
    {
        return [
            ['NEW UNIQUE ID', 'xxx', 'yyy', 'zzzzz'],
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
        return new RuleUpdater($this->familyRepositoryMock, $this->attributeRepositoryMock);
    }
}