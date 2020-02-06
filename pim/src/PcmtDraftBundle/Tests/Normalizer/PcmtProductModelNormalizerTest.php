<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductModelNormalizer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Normalizer\PcmtProductModelNormalizer;
use PHPUnit\Framework\TestCase;

class PcmtProductModelNormalizerTest extends TestCase
{
    /** @var ProductModelNormalizer */
    private $productModelNormalizerMock;

    /** @var EntityManager */
    private $entityManagerMock;

    /** @var ObjectRepository */
    private $repositoryMock;

    /** @var ProductInterface */
    private $productMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ObjectRepository::class);
        $this->productModelNormalizerMock = $this->createMock(ProductModelNormalizer::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->entityManagerMock->method('getRepository')->willReturn($this->repositoryMock);
        $this->productMock = $this->createMock(ProductInterface::class);
    }

    /**
     * @dataProvider dataNormalizeDifferentContext
     */
    public function testNormalizeDifferentContext(array $context, bool $expectedDraftIdSet): void
    {
        $this->productModelNormalizerMock->expects($this->once())->method('normalize')->willReturn([]);

        $normalizer = new PcmtProductModelNormalizer($this->productModelNormalizerMock, $this->entityManagerMock);
        $array = $normalizer->normalize($this->productMock, 'internal_api', $context);

        $this->assertSame($expectedDraftIdSet, isset($array['draftId']));
    }

    public function dataNormalizeDifferentContext(): array
    {
        return [
            'empty context'                => [[], false],
            'not empty context'            => [['xxx' => 1], false],
            'context with include draft'   => [['include_draft_id' => true], true],
            'context with include draft 2' => [[
                'xxx'              => 'yyy',
                'include_draft_id' => 1,
            ], true],
            'context with include draft set to false' => [['include_draft_id' => false], false],
        ];
    }

    /**
     * @dataProvider dataNormalizeForDifferentDrafts
     */
    public function testNormalizeForDifferentDrafts(?AbstractDraft $draft, int $expectedDraftId): void
    {
        $this->productModelNormalizerMock->expects($this->once())->method('normalize')->willReturn([]);

        $this->repositoryMock->method('findOneBy')->willReturn($draft);

        $normalizer = new PcmtProductModelNormalizer($this->productModelNormalizerMock, $this->entityManagerMock);
        $array = $normalizer->normalize($this->productMock, 'internal_api', ['include_draft_id' => true]);

        $this->assertSame($expectedDraftId, $array['draftId']);
    }

    public function dataNormalizeForDifferentDrafts(): array
    {
        $value = 2;
        $draft = $this->createMock(AbstractDraft::class);
        $draft->method('getId')->willReturn($value);

        return [
            [$draft, $value],
            [null, 0],
        ];
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(bool $value): void
    {
        $this->productModelNormalizerMock->expects($this->once())->method('supportsNormalization')->willReturn($value);
        $normalizer = new PcmtProductModelNormalizer($this->productModelNormalizerMock, $this->entityManagerMock);
        $result = $normalizer->supportsNormalization($this->productMock, 'internal_api');
        $this->assertSame($result, $value);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [true],
            [false],
        ];
    }
}