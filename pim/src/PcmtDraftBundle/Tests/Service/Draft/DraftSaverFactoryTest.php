<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

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
    private $productDraftSaver;

    /** @var ProductModelDraftSaver|MockObject */
    private $productModelDraftSaver;

    protected function setUp(): void
    {
        $this->productDraftSaver = $this->createMock(ProductDraftSaver::class);
        $this->productModelDraftSaver = $this->createMock(ProductModelDraftSaver::class);
        $this->factory = new DraftSaverFactory($this->productDraftSaver, $this->productModelDraftSaver);
    }

    public function draftsProvider(): array
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
     * @dataProvider draftsProvider
     *
     * @throws \ReflectionException
     */
    public function testFactoryWhenPassedDraftInstanceThenShouldReturnSpecifiedDraftSaver(
        string $class,
        string $expected
    ): void {
        $productDraft = $this->createMock($class);

        $this->assertInstanceOf($expected, $this->factory->create($productDraft));
    }
}