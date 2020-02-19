<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\ConcatenatedAttribute;

use Monolog\Logger;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use PcmtCoreBundle\Service\ConcatenatedAttribute\ConcatenatedAttributeCreator;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class ConcatenatedAttributeCreatorTest extends TestCase
{
    /** @var Attribute|Mock */
    private $attributeMock;

    /** @var Logger|Mock */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->attributeMock = $this->createMock(Attribute::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->attributeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
    }

    public function testUpdateConcatenatedAttributeValue(): void
    {
        $data = [
            'separator'  => '.',
            'attribute1' => 'Weight',
            'attribute2' => 'Height',
        ];

        $this->attributeMock->expects($this->once())
            ->method('getType')
            ->willReturn(PcmtAtributeTypes::CONCATENATED_FIELDS);

        $this->attributeMock->expects($this->exactly(count($data)))
            ->method('setProperty');

        $concatenatedAttributeCreator = new ConcatenatedAttributeCreator($this->loggerMock);

        $concatenatedAttributeCreator->update($this->attributeMock, 'concatenated', $data);
    }

    public function testShouldReturnNonChangedAttributeWhenInvalidParameters(): void
    {
        $data = [];

        $this->attributeMock->expects($this->once())
            ->method('getType')
            ->willReturn('Invalid_type');

        $concatenatedAttributeCreator = new ConcatenatedAttributeCreator($this->loggerMock);
        $return = $concatenatedAttributeCreator->update($this->attributeMock, 'notConcatenatedFieldType', $data);

        $this->assertSame($this->attributeMock, $return);
    }
}
