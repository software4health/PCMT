<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RuleAttributeProviderTest extends TestCase
{
    /** @var AttributeRepositoryInterface|MockObject */
    private $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
    }

    public function dataGetAllForFamilies(): array
    {
        $attribute1 = (new AttributeBuilder())->withType(AttributeTypes::IDENTIFIER)->withCode('A1')->build();
        $attribute2 = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->withCode('A2')->build();
        $attribute3 = (new AttributeBuilder())->withType(AttributeTypes::BOOLEAN)->withCode('A3')->build();
        $attribute4 = (new AttributeBuilder())->withType(AttributeTypes::OPTION_SIMPLE_SELECT)->withCode('A4')->build();
        $attributeUnique = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->asUnique()->withCode('A5')->build();
        $sourceFamily = (new FamilyBuilder())->withCode('XXX')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('YYY')->build();

        $map1 = [
            [$sourceFamily, [$attribute1]],
            [$destinationFamily, [$attribute1, $attribute2]],
        ];
        $expectedAttributes1 = [];

        $map2 = [
            [$sourceFamily, [$attribute1, $attribute3, $attribute4, $attributeUnique]],
            [$destinationFamily, [$attribute1, $attribute2, $attribute3, $attribute4, $attributeUnique]],
        ];
        $expectedAttributes2 = [$attribute3, $attribute4];

        return [
            [$sourceFamily, $destinationFamily, $map1, $expectedAttributes1],
            [$sourceFamily, $destinationFamily, $map2, $expectedAttributes2],
        ];
    }

    public function dataGetPossibleForKeyAttribute(): array
    {
        $attribute1 = (new AttributeBuilder())->withType(AttributeTypes::IDENTIFIER)->withCode('A1')->build();
        $attribute2 = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->withCode('A2')->build();
        $attribute3 = (new AttributeBuilder())->withType(AttributeTypes::BOOLEAN)->withCode('A3')->build();
        $attribute4 = (new AttributeBuilder())->withType(AttributeTypes::OPTION_SIMPLE_SELECT)->withCode('A4')->build();
        $attribute5 = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->asScopable()->withCode('A5')->build();
        $attributeUnique = (new AttributeBuilder())->withType(AttributeTypes::TEXT)->asUnique()->withCode('A6')->build();
        $sourceFamily = (new FamilyBuilder())->withCode('XXX')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('YYY')->build();

        $map1 = [
            [$sourceFamily, [$attribute1, $attributeUnique]],
            [$destinationFamily, [$attribute1, $attribute2, $attributeUnique]],
        ];
        $expectedAttributes1 = [];

        $map2 = [
            [$sourceFamily, [$attribute1, $attribute3, $attribute4, $attributeUnique]],
            [$destinationFamily, [$attribute1, $attribute2, $attribute3, $attribute4, $attributeUnique]],
        ];
        $expectedAttributes2 = [$attribute4];

        $map3 = [
            [$sourceFamily, [$attribute1, $attribute5]],
            [$destinationFamily, [$attribute1, $attribute2, $attribute5]],
        ];
        $expectedAttributes3 = [];

        return [
            [$sourceFamily, $destinationFamily, $map1, $expectedAttributes1],
            [$sourceFamily, $destinationFamily, $map2, $expectedAttributes2],
            [$sourceFamily, $destinationFamily, $map3, $expectedAttributes3],
        ];
    }

    /**
     * @dataProvider dataGetAllForFamilies
     */
    public function testGetAllForFamilies(Family $sourceFamily, Family $destinationFamily, array $valueMap, array $expectedAttributes): void
    {
        $this->attributeRepositoryMock->method('findAttributesByFamily')->will($this->returnValueMap($valueMap));
        $provider = $this->getRuleAttributeProviderInstance();
        $attributes = $provider->getAllForFamilies($sourceFamily, $destinationFamily);
        $this->assertEquals($expectedAttributes, $attributes);
    }

    /**
     * @dataProvider dataGetPossibleForKeyAttribute
     */
    public function testGetPossibleForKeyAttribute(Family $sourceFamily, Family $destinationFamily, array $valueMap, array $expectedAttributes): void
    {
        $this->attributeRepositoryMock->method('findAttributesByFamily')->will($this->returnValueMap($valueMap));
        $provider = $this->getRuleAttributeProviderInstance();
        $attributes = $provider->getPossibleForKeyAttribute($sourceFamily, $destinationFamily);
        $this->assertEquals($expectedAttributes, $attributes);
    }

    private function getRuleAttributeProviderInstance(): RuleAttributeProvider
    {
        return new RuleAttributeProvider($this->attributeRepositoryMock);
    }
}