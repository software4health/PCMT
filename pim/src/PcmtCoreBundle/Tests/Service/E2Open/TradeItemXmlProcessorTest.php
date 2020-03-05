<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Service\E2Open;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Monolog\Logger;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Service\E2Open\E2OpenAttributesService;
use PcmtCoreBundle\Service\E2Open\TradeItemXmlProcessor;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class TradeItemXmlProcessorTest extends TestCase
{
    /** @var Logger|Mock */
    private $loggerMock;

    /** @var E2OpenAttributesService|Mock */
    private $attributeServiceMock;

    /** @var ObjectUpdaterInterface|Mock */
    private $productUpdaterMock;

    /** @var ProductInterface|Mock */
    private $productMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);
        $this->attributeServiceMock = $this->createMock(E2OpenAttributesService::class);
        $this->productUpdaterMock = $this->createMock(ProductUpdater::class);
        $this->productMock = $this->createMock(Product::class);
    }

    /**
     * @dataProvider dataProcess
     */
    public function testProcess(array $node, array $updateSet): void
    {
        $tradeItemProcessor = $this->getTradeItemXmlProcessorInstance();

        $this->productUpdaterMock->expects($this->atLeastOnce())
            ->method('update')
            ->with(
                $this->productMock,
                [
                    'values' => $updateSet,
                ]
            );

        $this->attributeServiceMock->expects($this->atLeastOnce())
            ->method('getForCode')
            ->willReturn($attributeMock = $this->createMock(Attribute::class));

        $attributeMock->expects($this->atLeastOnce())
            ->method('getMetricFamily')
            ->willReturn(null);

        $tradeItemProcessor->setProductToUpdate($this->productMock);
        $tradeItemProcessor->processNode($node);
    }

    public function dataProcess(): array
    {
        return [
            [
                [
                    'name'       => '{}packaging',
                    'value'      => [
                        0 => [
                            'name'       => '{}packagingTypeCode',
                            'value'      => 'BPG',
                            'attributes' => [],
                        ],
                    ],
                    'attributes' => [
                        '{}packagingTypeCode' => 'BPG',
                    ],
                ],
                [
                    'GS1_PACKAGINGTYPECODE' => [
                        'data' => [
                            'data'   => 'BPG',
                            'locale' => null,
                            'scope'  => null,
                        ],
                    ],
                ],
            ],
            [
                [
                    'name'       => '{}tradeItemSynchronisationDates',
                    'value'      => [
                        0 => [
                            'name'       => '{}effectiveDateTime',
                            'value'      => '2019-11-26T00:00:00',
                            'attributes' => [],
                        ],
                    ],
                    'attributes' => [
                        '{}effectiveDateTime' => '2019-11-26T00:00:00',
                    ],
                ],
                [
                    'GS1_EFFECTIVEDATETIME' => [
                        'data' => [
                            'data'   => '2019-11-26T00:00:00',
                            'locale' => null,
                            'scope'  => null,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testProcessMetricUnitAttributes(): void
    {
        $node = [
            'name'  => '{}packaging',
            'value' => [
                0 => [
                    'name'       => '{}packagingTypeCode',
                    'value'      => 'BPG',
                    'attributes' => [
                        'measurementUnitCode' => 'code',
                    ],
                ],
            ],
        ];

        $tradeItemProcessor = $this->getTradeItemXmlProcessorInstance();

        $this->attributeServiceMock->expects($this->once())
            ->method('getForCode')
            ->willReturn($attributeMock = $this->createMock(Attribute::class));

        $attributeMock->expects($this->once())
            ->method('getMetricFamily')
            ->willReturn(E2OpenAttributesService::MEASURE_UNIT);

        $this->attributeServiceMock->expects($this->once())
            ->method('getMeasureUnitForSymbol');

        $tradeItemProcessor->setProductToUpdate($this->productMock);
        $tradeItemProcessor->processNode($node);
    }

    public function testErrorWhenMappingNotFound(): void
    {
        $node = [
            'name'  => '{}packaging',
            'value' => [
                0 => [
                    'name'       => '{}packagingTypeCode',
                    'value'      => 'BPG',
                    'attributes' => [],
                ],
            ],
        ];

        $tradeItemProcessor = $this->getTradeItemXmlProcessorInstance();

        $this->attributeServiceMock->expects($this->once())
            ->method('getForCode')
            ->with('GS1_PACKAGINGTYPECODE')
            ->willReturn(null);

        $this->loggerMock->expects($this->once())
            ->method('error');

        $tradeItemProcessor->setProductToUpdate($this->productMock);
        $tradeItemProcessor->processNode($node);
    }

    private function getTradeItemXmlProcessorInstance(): TradeItemXmlProcessor
    {
        return new TradeItemXmlProcessor(
            $this->loggerMock,
            $this->attributeServiceMock,
            $this->productUpdaterMock
        );
    }
}
