<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use PcmtDraftBundle\Service\Draft\BaseProductCreatorForDraft;
use PcmtDraftBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseProductCreatorForDraftTest extends TestCase
{
    /** @var BaseProductCreatorForDraft */
    private $baseProductCreator;

    /** @var ProductBuilderInterface|MockObject */
    private $productBuilder;

    protected function setUp(): void
    {
        $this->productBuilder = $this->createMock(ProductBuilderInterface::class);

        $this->baseProductCreator = new BaseProductCreatorForDraft($this->productBuilder);
    }

    public function testCreateFromProcessedProduct(): void
    {
        $product = (new ProductBuilder())
            ->withFamily((new FamilyBuilder())->build())
            ->build();

        $this->productBuilder
            ->expects($this->once())
            ->method('createProduct')
            ->with($product->getIdentifier(), $product->getFamily()->getCode());

        $this->baseProductCreator->create($product);
    }
}