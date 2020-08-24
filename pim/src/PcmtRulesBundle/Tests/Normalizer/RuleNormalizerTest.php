<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\Normalizer;

use PcmtRulesBundle\Normalizer\RuleNormalizer;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\RuleBuilder;
use PHPUnit\Framework\TestCase;

class RuleNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $rule = (new RuleBuilder())->build();

        $normalizer = new RuleNormalizer();
        $array = $normalizer->normalize($rule);

        $this->assertIsArray($array);
        $this->assertSame($rule->getUniqueId(), $array['uniqueId']);
        $this->assertSame($rule->getId(), $array['id']);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $normalizer = new RuleNormalizer();
        $result = $normalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [(new RuleBuilder())->build(), true],
            [(new FamilyBuilder())->build(), false],
        ];
    }
}