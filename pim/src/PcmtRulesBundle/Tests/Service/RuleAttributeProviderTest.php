<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

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

    public function dataGetForFamilies(): array
    {
        $attribute1 = (new AttributeBuilder())->withCode('A1')->build();
        $attribute2 = (new AttributeBuilder())->withCode('A2')->build();
        $attribute3 = (new AttributeBuilder())->withCode('A3')->build();
        $attribute4 = (new AttributeBuilder())->withCode('A4')->build();
        $sourceFamily = (new FamilyBuilder())->withCode('XXX')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('YYY')->build();

        $map1 = [
            [$sourceFamily, [$attribute1]],
            [$destinationFamily, [$attribute1, $attribute2]],
        ];
        $expectedAttributes1 = [$attribute1];

        $map2 = [
            [$sourceFamily, [$attribute1, $attribute3, $attribute4]],
            [$destinationFamily, [$attribute1, $attribute2, $attribute3]],
        ];
        $expectedAttributes2 = [$attribute1, $attribute3];

        return [
            [$sourceFamily, $destinationFamily, $map1, $expectedAttributes1],
            [$sourceFamily, $destinationFamily, $map2, $expectedAttributes2],
        ];
    }

    /**
     * @dataProvider dataGetForFamilies
     */
    public function testGetForFamilies(Family $sourceFamily, Family $destinationFamily, array $valueMap, array $expectedAttributes): void
    {
        $this->attributeRepositoryMock->method('findAttributesByFamily')->will($this->returnValueMap($valueMap));
        $provider = $this->getRuleAttributeProviderInstance();
        $attributes = $provider->getForFamilies($sourceFamily, $destinationFamily);
        $this->assertEquals($expectedAttributes, $attributes);
    }

    private function getRuleAttributeProviderInstance(): RuleAttributeProvider
    {
        return new RuleAttributeProvider($this->attributeRepositoryMock);
    }
}