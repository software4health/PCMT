<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Saver\ProductDraftSaver;
use PcmtDraftBundle\Saver\ProductModelDraftSaver;
use PcmtDraftBundle\Service\Draft\DraftSaverFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftSaverFactoryTest extends TestCase
{
    /** @var DraftSaverFactory */
    private $factory;

    /** @var ProductDraftSaver|MockObject */
    private $productDraftSaverMock;

    /** @var ProductModelDraftSaver|MockObject */
    private $productModelDraftSaverMock;

    protected function setUp(): void
    {
        $this->productDraftSaverMock = $this->createMock(ProductDraftSaver::class);
        $this->productModelDraftSaverMock = $this->createMock(ProductModelDraftSaver::class);
        $this->factory = new DraftSaverFactory($this->productDraftSaverMock, $this->productModelDraftSaverMock);
    }

    /**
     * @dataProvider dataCreate
     *
     * @throws \ReflectionException
     */
    public function testCreate(string $class, string $expected): void
    {
        $productDraftMock = $this->createMock($class);
        $this->assertInstanceOf($expected, $this->factory->create($productDraftMock));
    }

    public function dataCreate(): array
    {
        return [
            'product_draft'       => [
                'class'    => ProductDraftInterface::class,
                'expected' => ProductDraftSaver::class,
            ],
            'product_model_draft' => [
                'class'    => ProductModelDraftInterface::class,
                'expected' => ProductModelDraftSaver::class,
            ],
        ];
    }

    /**
     * @dataProvider dataCreate
     *
     * @throws \ReflectionException
     */
    public function testCreateThrowsException(): void
    {
        $objectMock = $this->createMock(DraftInterface::class);
        $this->expectException(\Throwable::class);
        $this->factory->create($objectMock);
    }
}