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
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
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
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelDraftNormalizerTest extends TestCase
{
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

    /** @var GeneralObjectFromDraftCreator|MockObject */
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

        $this->creator = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creator->method('getObjectToCompare')->willReturn($this->productModelNew);

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
        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = $this->createMock(NewProductModelDraft::class);
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $draft->method('getAuthor')->willReturn($author);
        $draft->method('getProductModel')->willReturn(null);
        $draft->method('getStatus')->willReturn(AbstractDraft::STATUS_NEW);
        $draft->method('getType')->willReturn(NewProductModelDraft::TYPE);

        $array = $productModelDraftNormalizer->normalize($draft);

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

        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = $this->createMock(NewProductModelDraft::class);
        $draft->method('getProductModel')->willReturn(null);
        $draft->method('getType')->willReturn(NewProductModelDraft::TYPE);

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeChangesExistingProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->attributeChangeService->method('get')->willReturn($changes);

        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);
        $draft->method('getType')->willReturn(ExistingProductModelDraft::TYPE);

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
        $this->assertCount(1, $array['values']['values']);
    }

    public function testNormalizeWhenContextIsEmptyThenShouldNotReturnProduct(): void
    {
        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);
        $draft->method('getType')->willReturn(ExistingProductModelDraft::TYPE);

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertArrayNotHasKey('product', $array);
    }

    public function testNormalizeWhenContextHasProductIncludedThenShouldReturnNormalizedProduct(): void
    {
        $formName = 'form-xxx';
        $this->formProvider->method('getForm')->willReturn($formName);

        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);
        $draft->method('getType')->willReturn(ExistingProductModelDraft::TYPE);

        $array = $productModelDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
        $this->assertSame($formName, $array['product']['meta']['form']);
    }

    public function testNormalizeWhenNoProductModelToCompare(): void
    {
        $this->creator = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creator->method('getObjectToCompare')->willReturn(null);

        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getType')->willReturn(ExistingProductModelDraft::TYPE);

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertIsArray($array);
        $this->assertArrayNotHasKey('label', $array);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $normalizer = $this->getProductModelDraftNormalizerInstance();
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

    private function getProductModelDraftNormalizerInstance(): ProductModelDraftNormalizer
    {
        $normalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productModelNormalizer
        );
        $normalizer->setProductModelFromDraftCreator($this->creator);
        $normalizer->setAttributeChangeService($this->attributeChangeService);
        $normalizer->setValuesNormalizer($this->valuesNormalizer);
        $normalizer->setDatetimePresenter($this->createMock(PresenterInterface::class));

        return $normalizer;
    }
}