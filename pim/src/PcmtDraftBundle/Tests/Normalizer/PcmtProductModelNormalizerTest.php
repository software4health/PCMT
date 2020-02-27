<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductModelNormalizer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Normalizer\PcmtProductModelNormalizer;
use PcmtDraftBundle\Service\Helper\UnexpectedAttributesFilter;
use PHPUnit\Framework\TestCase;

class PcmtProductModelNormalizerTest extends TestCase
{
    /** @var ProductModelNormalizer */
    private $productModelNormalizerMock;

    /** @var EntityManager */
    private $entityManagerMock;

    /** @var ObjectRepository */
    private $repositoryMock;

    /** @var ProductModelInterface */
    private $productModelMock;

    /** @var UnexpectedAttributesFilter */
    private $unexpectedAttributesFilterMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ObjectRepository::class);
        $this->productModelNormalizerMock = $this->createMock(ProductModelNormalizer::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->entityManagerMock->method('getRepository')->willReturn($this->repositoryMock);
        $this->productModelMock = $this->createMock(ProductModelInterface::class);
        $this->unexpectedAttributesFilterMock = $this->createMock(UnexpectedAttributesFilter::class);
    }

    /**
     * @dataProvider dataNormalizeDifferentContext
     */
    public function testNormalizeDifferentContext(array $context, bool $expectedDraftIdSet): void
    {
        $this->productModelNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $normalizer = new PcmtProductModelNormalizer(
            $this->productModelNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $array = $normalizer->normalize($this->productModelMock, 'internal_api', $context);

        $this->assertSame($expectedDraftIdSet, isset($array['draftId']));
    }

    public function dataNormalizeDifferentContext(): array
    {
        return [
            'empty context'                           => [
                [],
                false,
            ],
            'not empty context'                       => [
                ['xxx' => 1],
                false,
            ],
            'context with include draft'              => [
                ['include_draft_id' => true],
                true,
            ],
            'context with include draft 2'            => [
                [
                    'xxx'              => 'yyy',
                    'include_draft_id' => 1,
                ],
                true,
            ],
            'context with include draft set to false' => [
                ['include_draft_id' => false],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataNormalizeForDifferentDrafts
     */
    public function testNormalizeForDifferentDrafts(?AbstractDraft $draft, int $expectedDraftId): void
    {
        $this->productModelNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->repositoryMock->method('findOneBy')->willReturn($draft);

        $normalizer = new PcmtProductModelNormalizer(
            $this->productModelNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $array = $normalizer->normalize($this->productModelMock, 'internal_api', ['include_draft_id' => true]);

        $this->assertSame($expectedDraftId, $array['draftId']);
    }

    public function dataNormalizeForDifferentDrafts(): array
    {
        $value = 2;
        $draft = $this->createMock(AbstractDraft::class);
        $draft->method('getId')->willReturn($value);

        return [
            [
                $draft,
                $value,
            ],
            [
                null,
                0,
            ],
        ];
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(bool $value): void
    {
        $this->productModelNormalizerMock
            ->expects($this->once())
            ->method('supportsNormalization')
            ->willReturn($value);

        $normalizer = new PcmtProductModelNormalizer(
            $this->productModelNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $result = $normalizer->supportsNormalization($this->productModelMock, 'internal_api');
        $this->assertSame($result, $value);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContext(): void
    {
        $this->productModelNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn(
                [
                    'values' => [],
                ]
            );

        $this->unexpectedAttributesFilterMock
            ->expects($this->once())
            ->method('filter');

        $normalizer = new PcmtProductModelNormalizer(
            $this->productModelNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $normalizer->normalize($this->productModelMock, 'standard', ['import_via_drafts' => true]);
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContextAndProductModelIsRoot(): void
    {
        $this->productModelNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->unexpectedAttributesFilterMock
            ->expects($this->never())
            ->method('filter');

        $this->productModelMock
            ->method('isRoot')
            ->willReturn(true);

        $normalizer = new PcmtProductModelNormalizer(
            $this->productModelNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $normalizer->normalize($this->productModelMock, 'standard', ['import_via_drafts' => true]);
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContextAndNormalizedDataHasNotValuesKey(): void
    {
        $this->productModelNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->unexpectedAttributesFilterMock
            ->expects($this->never())
            ->method('filter');

        $normalizer = new PcmtProductModelNormalizer(
            $this->productModelNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $normalizer->normalize($this->productModelMock, 'standard', ['import_via_drafts' => true]);
    }
}