<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Normalizer\PcmtProductNormalizer;
use PcmtDraftBundle\Service\Helper\UnexpectedAttributesFilter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PcmtProductNormalizerTest extends TestCase
{
    /** @var NormalizerInterface */
    private $productNormalizerMock;

    /** @var EntityManager */
    private $entityManagerMock;

    /** @var ObjectRepository */
    private $repositoryMock;

    /** @var ProductInterface */
    private $productMock;

    /** @var UnexpectedAttributesFilter */
    private $unexpectedAttributesFilterMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ObjectRepository::class);
        $this->productNormalizerMock = $this->createMock(ProductNormalizer::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->entityManagerMock->method('getRepository')->willReturn($this->repositoryMock);
        $this->productMock = $this->createMock(ProductInterface::class);
        $this->unexpectedAttributesFilterMock = $this->createMock(UnexpectedAttributesFilter::class);
    }

    /**
     * @dataProvider dataNormalizeDifferentContext
     */
    public function testNormalizeDifferentContext(array $context, bool $expectedDraftIdSet): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $normalizer = new PcmtProductNormalizer(
            $this->productNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $array = $normalizer->normalize($this->productMock, 'internal_api', $context);

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
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->repositoryMock->method('findOneBy')->willReturn($draft);

        $normalizer = new PcmtProductNormalizer(
            $this->productNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $array = $normalizer->normalize($this->productMock, 'internal_api', ['include_draft_id' => true]);

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
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('supportsNormalization')
            ->willReturn($value);

        $normalizer = new PcmtProductNormalizer(
            $this->productNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

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

    public function testNormalizeWhenImportViaDraftsIsSetInContext(): void
    {
        $this->productNormalizerMock
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

        $this->productMock
            ->method('isVariant')
            ->willReturn(true);

        $normalizer = new PcmtProductNormalizer(
            $this->productNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $normalizer->normalize($this->productMock, 'standard', ['import_via_drafts' => true]);
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContextAndProductIsNotVariant(): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->unexpectedAttributesFilterMock
            ->expects($this->never())
            ->method('filter');

        $this->productMock
            ->method('isVariant')
            ->willReturn(false);

        $normalizer = new PcmtProductNormalizer(
            $this->productNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $normalizer->normalize($this->productMock, 'standard', ['import_via_drafts' => true]);
    }

    public function testNormalizeWhenImportViaDraftsIsSetInContextAndNormalizedDataHasNotValuesKey(): void
    {
        $this->productNormalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->willReturn([]);

        $this->unexpectedAttributesFilterMock
            ->expects($this->never())
            ->method('filter');

        $this->productMock
            ->method('isVariant')
            ->willReturn(false);

        $normalizer = new PcmtProductNormalizer(
            $this->productNormalizerMock,
            $this->entityManagerMock,
            $this->unexpectedAttributesFilterMock
        );

        $normalizer->normalize($this->productMock, 'standard', ['import_via_drafts' => true]);
    }
}