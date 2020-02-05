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
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtCoreBundle\Entity\Attribute;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\AttributeChange;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Normalizer\AttributeChangeNormalizer;
use PcmtDraftBundle\Normalizer\DraftStatusNormalizer;
use PcmtDraftBundle\Normalizer\ProductModelDraftNormalizer;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
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

    /** @var AttributeChangeService */
    private $attributeChangeService;

    /** @var FormProviderInterface */
    private $formProvider;

    /** @var FamilyInterface|MockObject */
    private $family;

    /** @var FamilyVariantInterface|MockObject */
    private $familyVariant;

    /** @var NormalizerInterface|MockObject */
    private $valuesNormalizer;

    protected function setUp(): void
    {
        $this->family = $this->createMock(FamilyInterface::class);

        $this->productModelNew = $this->createMock(ProductModel::class);
        $this->productModelNew->method('getFamily')->willReturn($this->family);
        $valueCollection = new WriteValueCollection();
        $value = $this->createMock(ValueInterface::class);
        $valueCollection->add($value);
        $this->productModelNew->method('getValues')->willReturn($valueCollection);

        $this->productModelExisting = $this->createMock(ProductModel::class);
        $this->productModelExisting->method('getFamily')->willReturn($this->family);

        $this->attributeChangeNormalizer = new AttributeChangeNormalizer();
        $logger = $this->createMock(LoggerInterface::class);
        $translator = $this->createMock(DraftStatusTranslatorService::class);
        $this->draftStatusNormalizer = new DraftStatusNormalizer($logger, $translator);

        $this->creator = $this->createMock(ProductModelFromDraftCreator::class);
        $this->creator->method('getProductModelToCompare')->willReturn($this->productModelNew);

        $this->attributeChangeService = $this->createMock(AttributeChangeService::class);
        $this->productModelNormalizer = $this->createMock(NormalizerInterface::class);

        $this->formProvider = $this->createMock(FormProviderInterface::class);

        $this->familyVariant = $this->createMock(FamilyVariantInterface::class);
        $this->productModelNew->method('getFamilyVariant')->willReturn($this->familyVariant);
        $this->productModelExisting->method('getFamilyVariant')->willReturn($this->familyVariant);

        $this->valuesNormalizer = $this->createMock(NormalizerInterface::class);
        $this->valuesNormalizer->method('normalize')->willReturn([]);
        $this->valuesNormalizer->method('supportsNormalization')->willReturn(true);

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
        $this->productModelDraftNormalizer->setAttributeChangeService($this->attributeChangeService);
        $this->productModelDraftNormalizer->setValuesNormalizer($this->valuesNormalizer);

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
        $this->productModelDraftNormalizer->setAttributeChangeService($this->attributeChangeService);
        $this->productModelDraftNormalizer->setValuesNormalizer($this->valuesNormalizer);
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
        $this->productModelDraftNormalizer->setAttributeChangeService($this->attributeChangeService);
        $this->productModelDraftNormalizer->setValuesNormalizer($this->valuesNormalizer);

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);

        $array = $this->productModelDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
        $this->assertCount(1, $array['values']['values']);
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
        $this->productModelDraftNormalizer->setAttributeChangeService($this->attributeChangeService);
        $this->productModelDraftNormalizer->setValuesNormalizer($this->valuesNormalizer);

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);

        $array = $this->productModelDraftNormalizer->normalize($draft);

        $this->assertArrayNotHasKey('product', $array);
    }

    public function testNormalizeWhenContextHasProductIncludedThenShouldReturnNormalizedProduct(): void
    {
        $formName = 'form-xxx';
        $this->formProvider->method('getForm')->willReturn($formName);

        $productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $productModelDraftNormalizer->setAttributeChangeService($this->attributeChangeService);
        $productModelDraftNormalizer->setValuesNormalizer($this->valuesNormalizer);

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);

        $array = $productModelDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
        $this->assertSame($formName, $array['product']['meta']['form']);
    }

    public function testNormalizeWhenNoProductModelToCompare(): void
    {
        $this->creator = $this->createMock(ProductModelFromDraftCreator::class);
        $this->creator->method('getProductModelToCompare')->willReturn(null);

        $productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $productModelDraftNormalizer->setAttributeChangeService($this->attributeChangeService);
        $productModelDraftNormalizer->setValuesNormalizer($this->valuesNormalizer);

        $draft = $this->createMock(ExistingProductModelDraft::class);

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertIsArray($array);
        $this->assertArrayNotHasKey('label', $array);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $normalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $result = $normalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [$this->createMock(ProductModelDraftInterface::class), true],
            [$this->createMock(Attribute::class), false],
        ];
    }
}