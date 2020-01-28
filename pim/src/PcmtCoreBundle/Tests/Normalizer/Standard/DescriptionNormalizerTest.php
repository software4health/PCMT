<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PcmtCoreBundle\Entity\AttributeTranslation;
use PcmtCoreBundle\Normalizer\Standard\DescriptionNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DescriptionNormalizerTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testNormalize(AttributeInterface $attribute, int $expectedCount, ?string $firstDesc): void
    {
        $descriptionNormalizer = new DescriptionNormalizer();
        $array = $descriptionNormalizer->normalize($attribute);

        $this->assertIsArray($array);
        $this->assertCount($expectedCount, $array);
        if ($firstDesc) {
            $firstResult = reset($array);
            $this->assertSame($firstDesc, $firstResult);
        }
    }

    public function provideData(): array
    {
        /** @var AttributeTranslation|MockObject $englishTranslation */
        $englishTranslation = $this->createMock(AttributeTranslation::class);
        $englishTranslation->method('getDescription')->willReturn('english desc');
        $englishTranslation->method('getLocale')->willReturn('en_US');
        /** @var AttributeTranslation|MockObject $frenchTranslation */
        $frenchTranslation = $this->createMock(AttributeTranslation::class);
        $frenchTranslation->method('getDescription')->willReturn('french desc');
        $frenchTranslation->method('getLocale')->willReturn('fr');

        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute1->method('getTranslations')->willReturn([$englishTranslation, $frenchTranslation]);

        $attribute2 = $this->createMock(AttributeInterface::class);
        $attribute2->method('getTranslations')->willReturn([$frenchTranslation]);

        $attribute3 = $this->createMock(AttributeInterface::class);
        $attribute3->method('getTranslations')->willReturn([]);

        return [
            'attribute with 2 translations' => [$attribute1, 2, $englishTranslation->getDescription()],
            'attribute with 1 translation'  => [$attribute2, 1, $frenchTranslation->getDescription()],
            'attribute with 0 translations' => [$attribute3, 0, null],
        ];
    }
}