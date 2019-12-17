<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\AttributeChange;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Entity\AttributeChange;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class AttributeChangeServiceTest extends TestCase
{
    /** @var MockObject|Serializer */
    private $versioningSerializerMock;

    /** @var MockObject|AttributeRepositoryInterface */
    protected $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->versioningSerializerMock = $this->createMock(Serializer::class);
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        parent::setUp();
    }

    private function getAttributeChangeServiceInstance(): FakeAttributeChangeService
    {
        return new FakeAttributeChangeService(
            $this->versioningSerializerMock,
            $this->attributeRepositoryMock
        );
    }

    private function getAttributeName(AttributeChange $entity): string
    {
        return $entity->getAttributeName();
    }

    /**
     * @dataProvider dataForCreateChangeTest
     *
     * @throws \ReflectionException
     */
    public function testCreateChangeFunction(string $attributeCode, ?string $attributeLabel): void
    {
        $attributeChangeService = $this->getAttributeChangeServiceInstance();
        $attributeInstanceMock = null;
        if (null !== $attributeLabel) {
            $attributeInstanceMock = $this->createMock(Attribute::class);
            $attributeInstanceMock->method('getLabel')->willReturn($attributeLabel);
        } else {
            $attributeLabel = $attributeCode;
        }
        $this->attributeRepositoryMock->method('findOneByIdentifier')->willReturn($attributeInstanceMock);
        $attributeChangeService->createChange($attributeCode, 'zm1', 'zm2');
        $this->assertSame($attributeLabel, $this->getAttributeName($attributeChangeService->getChanges()[0]));
    }

    public function dataForCreateChangeTest(): array
    {
        return [
            'an attribute'     => [
                'attributeCode'   => 'attribute_1',
                'attributeLabel'  => 'Attribute 1',
            ],
            'not an attribute' => [
                'attributeCode'   => 'attribute_1',
                'attributeLabel'  => null,
            ],
        ];
    }
}