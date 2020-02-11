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
        $tradeItemProcessor = new TradeItemXmlProcessor(
            $this->loggerMock,
            $this->attributeServiceMock,
            $this->productUpdaterMock
        );

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

        $attributeMock->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('GS1_PACKAGINGTYPECODE');

        $tradeItemProcessor->setProductToUpdate($this->productMock);
        $tradeItemProcessor->processNode($node);
    }

    public function dataProcess(): array
    {
        return [
            [
                [
                    'name'  => '{}packaging',
                    'value' => [
                        0 => [
                            'name'       => '{}packagingTypeCode',
                            'value'      => 'BPG',
                            'attributes' => [],
                        ],
                    ],
                    'attributes' => [],
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
        ];
    }
}
