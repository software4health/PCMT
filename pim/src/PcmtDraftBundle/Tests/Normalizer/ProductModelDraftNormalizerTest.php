<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\AttributeChange;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Normalizer\AttributeChangeNormalizer;
use PcmtDraftBundle\Normalizer\DraftStatusNormalizer;
use PcmtDraftBundle\Normalizer\ProductModelDraftNormalizer;
use PcmtDraftBundle\Service\AttributeChange\ProductModelAttributeChangeService;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use PcmtDraftBundle\Service\Draft\ProductModelFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelDraftNormalizerTest extends TestCase
{
    /** @var ProductModelDraftNormalizer */
    private $productModelDraftNormalizer;

    /** @var ProductModelInterface|MockObject */
    private $productModelNew;

    /** @var ProductModelInterface|MockObject */
    private $productModelExisting;

    /** @var DraftStatusNormalizer */
    private $draftStatusNormalizer;

    /** @var AttributeChangeNormalizer */
    private $attributeChangeNormalizer;

    /** @var NormalizerInterface|MockObject */
    private $productModelNormalizer;

    /** @var ProductModelFromDraftCreator|MockObject */
    private $creator;

    /** @var ProductModelAttributeChangeService */
    private $attributeChangeService;

    /** @var FormProviderInterface */
    private $formProvider;

    protected function setUp(): void
    {
        $this->productModelNew = $this->createMock(ProductModel::class);
        $this->productModelExisting = $this->createMock(ProductModel::class);

        $this->attributeChangeNormalizer = new AttributeChangeNormalizer();
        $logger = $this->createMock(LoggerInterface::class);
        $translator = $this->createMock(DraftStatusTranslatorService::class);
        $this->draftStatusNormalizer = new DraftStatusNormalizer($logger, $translator);

        $this->creator = $this->createMock(ProductModelFromDraftCreator::class);
        $this->creator->method('getProductModelToCompare')->willReturn($this->productModelNew);

        $this->attributeChangeService = $this->createMock(ProductModelAttributeChangeService::class);
        $this->productModelNormalizer = $this->createMock(NormalizerInterface::class);

        $this->formProvider = $this->createMock(FormProviderInterface::class);

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $this->productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $this->productModelDraftNormalizer->setProductModelAttributeChangeService($this->attributeChangeService);

        $draft = $this->createMock(NewProductModelDraft::class);
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $draft->method('getAuthor')->willReturn($author);
        $draft->method('getProductModel')->willReturn(null);
        $draft->method('getStatus')->willReturn(AbstractDraft::STATUS_NEW);

        $array = $this->productModelDraftNormalizer->normalize($draft);

        $this->assertEmpty($array['changes']);
        $this->assertSame('Alfred Nobel', $array['author']);
        $this->assertIsArray($array['status']);
        $this->assertArrayHasKey('id', $array['status']);
        $this->assertArrayHasKey('name', $array['status']);
    }

    public function testNormalizeChangesNewProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->attributeChangeService->method('get')->willReturn($changes);

        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $this->productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $this->productModelDraftNormalizer->setProductModelAttributeChangeService($this->attributeChangeService);
        $draft = $this->createMock(NewProductModelDraft::class);
        $draft->method('getProductModel')->willReturn(null);

        $array = $this->productModelDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeChangesExistingProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->attributeChangeService->method('get')->willReturn($changes);

        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $this->productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $this->productModelDraftNormalizer->setProductModelAttributeChangeService($this->attributeChangeService);

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);

        $array = $this->productModelDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeWhenContextIsEmptyThenShouldNotReturnProduct(): void
    {
        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $this->productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $this->productModelDraftNormalizer->setProductModelAttributeChangeService($this->attributeChangeService);

        $draft = $this->createMock(ExistingProductModelDraft::class);

        $array = $this->productModelDraftNormalizer->normalize($draft);

        $this->assertArrayNotHasKey('product', $array);
    }

    public function testNormalizeWhenContextHasProductIncludedThenShouldReturnNormalizedProduct(): void
    {
        $formName = 'form-xxx';
        $this->formProvider->method('getForm')->willReturn($formName);

        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $this->productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $this->productModelDraftNormalizer->setProductModelAttributeChangeService($this->attributeChangeService);

        $draft = $this->createMock(ExistingProductModelDraft::class);

        $array = $this->productModelDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
        $this->assertSame($formName, $array['product']['meta']['form']);
    }
}