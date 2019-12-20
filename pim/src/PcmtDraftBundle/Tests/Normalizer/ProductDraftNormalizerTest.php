<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\AttributeChange;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Normalizer\AttributeChangeNormalizer;
use PcmtDraftBundle\Normalizer\DraftStatusNormalizer;
use PcmtDraftBundle\Normalizer\ProductDraftNormalizer;
use PcmtDraftBundle\Service\AttributeChange\ProductAttributeChangeService;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use PcmtDraftBundle\Service\Draft\ProductFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDraftNormalizerTest extends TestCase
{
    /** @var ProductDraftNormalizer */
    private $productDraftNormalizer;

    /** @var ProductInterface|MockObject */
    private $productNew;

    /** @var ProductInterface|MockObject */
    private $productExisting;

    /** @var DraftStatusNormalizer */
    private $draftStatusNormalizer;

    /** @var AttributeChangeNormalizer */
    private $attributeChangeNormalizer;

    /** @var NormalizerInterface|MockObject */
    private $productNormalizer;

    /** @var ProductFromDraftCreator|MockObject */
    private $creator;

    /** @var ProductAttributeChangeService */
    private $productAttributeChangeService;

    /** @var FormProviderInterface */
    private $formProvider;

    protected function setUp(): void
    {
        $this->productNew = $this->createMock(Product::class);
        $this->productExisting = $this->createMock(Product::class);

        $this->attributeChangeNormalizer = new AttributeChangeNormalizer();
        $logger = $this->createMock(LoggerInterface::class);
        $translator = $this->createMock(DraftStatusTranslatorService::class);
        $this->draftStatusNormalizer = new DraftStatusNormalizer($logger, $translator);

        $this->creator = $this->createMock(ProductFromDraftCreator::class);
        $this->creator->method('getProductToCompare')->willReturn($this->productNew);

        $this->productAttributeChangeService = $this->createMock(ProductAttributeChangeService::class);
        $this->productNormalizer = $this->createMock(NormalizerInterface::class);

        $this->formProvider = $this->createMock(FormProviderInterface::class);

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productNormalizer
        );
        $this->productDraftNormalizer->setProductFromDraftCreator($this->creator);
        $this->productDraftNormalizer->setProductAttributeChangeService($this->productAttributeChangeService);

        $draft = $this->createMock(NewProductDraft::class);
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $draft->method('getAuthor')->willReturn($author);
        $draft->method('getProduct')->willReturn(null);
        $draft->method('getStatus')->willReturn(AbstractDraft::STATUS_NEW);

        $array = $this->productDraftNormalizer->normalize($draft);

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
        $this->productAttributeChangeService->method('get')->willReturn($changes);

        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productNormalizer
        );
        $this->productDraftNormalizer->setProductFromDraftCreator($this->creator);
        $this->productDraftNormalizer->setProductAttributeChangeService($this->productAttributeChangeService);
        $draft = $this->createMock(NewProductDraft::class);
        $draft->method('getProduct')->willReturn(null);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeChangesExistingProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->productAttributeChangeService->method('get')->willReturn($changes);

        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productNormalizer
        );
        $this->productDraftNormalizer->setProductFromDraftCreator($this->creator);
        $this->productDraftNormalizer->setProductAttributeChangeService($this->productAttributeChangeService);

        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getProduct')->willReturn($this->productExisting);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeWhenContextIsEmptyThenShouldNotReturnProduct(): void
    {
        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productNormalizer
        );

        $this->productDraftNormalizer->setProductFromDraftCreator($this->creator);
        $this->productDraftNormalizer->setProductAttributeChangeService($this->productAttributeChangeService);

        $draft = $this->createMock(ExistingProductDraft::class);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertArrayNotHasKey('product', $array);
    }

    public function testNormalizeWhenContextHasProductIncludedThenShouldReturnNormalizedProduct(): void
    {
        $formName = 'form-xxx';
        $this->formProvider->method('getForm')->willReturn($formName);

        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productNormalizer
        );

        $this->productDraftNormalizer->setProductFromDraftCreator($this->creator);
        $this->productDraftNormalizer->setProductAttributeChangeService($this->productAttributeChangeService);

        $draft = $this->createMock(ExistingProductDraft::class);

        $array = $this->productDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
        $this->assertSame($formName, $array['product']['meta']['form']);
    }
}