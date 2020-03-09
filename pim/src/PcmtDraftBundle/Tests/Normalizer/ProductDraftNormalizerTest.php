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
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtCoreBundle\Entity\Attribute;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\AttributeChange;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Normalizer\AttributeChangeNormalizer;
use PcmtDraftBundle\Normalizer\DraftStatusNormalizer;
use PcmtDraftBundle\Normalizer\ProductDraftNormalizer;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
use PcmtDraftBundle\Service\Draft\DraftStatusTranslatorService;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
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

    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $creator;

    /** @var AttributeChangeService */
    private $attributeChangeService;

    /** @var FormProviderInterface */
    private $formProvider;

    /** @var FamilyInterface|MockObject */
    private $family;

    /** @var NormalizerInterface|MockObject */
    private $valuesNormalizer;

    protected function setUp(): void
    {
        $this->family = $this->createMock(FamilyInterface::class);

        $this->productNew = $this->createMock(Product::class);
        $this->productNew->method('getFamily')->willReturn($this->family);
        $valueCollection = new WriteValueCollection();
        $value = $this->createMock(ValueInterface::class);
        $valueCollection->add($value);
        $this->productNew->method('getValues')->willReturn($valueCollection);

        $this->productExisting = $this->createMock(Product::class);
        $this->productExisting->method('getFamily')->willReturn($this->family);

        $this->attributeChangeNormalizer = new AttributeChangeNormalizer();
        $logger = $this->createMock(LoggerInterface::class);
        $translator = $this->createMock(DraftStatusTranslatorService::class);
        $this->draftStatusNormalizer = new DraftStatusNormalizer($logger, $translator);

        $this->creator = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creator->method('getObjectToCompare')->willReturn($this->productNew);

        $this->attributeChangeService = $this->createMock(AttributeChangeService::class);
        $this->productNormalizer = $this->createMock(NormalizerInterface::class);

        $this->formProvider = $this->createMock(FormProviderInterface::class);

        $this->valuesNormalizer = $this->createMock(NormalizerInterface::class);
        $this->valuesNormalizer->method('normalize')->willReturn([]);
        $this->valuesNormalizer->method('supportsNormalization')->willReturn(true);

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $this->productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = $this->createMock(NewProductDraft::class);
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $draft->method('getAuthor')->willReturn($author);
        $draft->method('getProduct')->willReturn(null);
        $draft->method('getStatus')->willReturn(AbstractDraft::STATUS_NEW);
        $draft->method('getType')->willReturn(NewProductDraft::TYPE);

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
        $this->attributeChangeService->method('get')->willReturn($changes);

        $this->productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = $this->createMock(NewProductDraft::class);
        $draft->method('getProduct')->willReturn(null);
        $draft->method('getType')->willReturn(NewProductDraft::TYPE);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeChangesExistingProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->attributeChangeService->method('get')->willReturn($changes);

        $this->productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getProduct')->willReturn($this->productExisting);
        $draft->method('getType')->willReturn(ExistingProductDraft::TYPE);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
        $this->assertCount(1, $array['values']['values']);
    }

    public function testNormalizeWhenContextIsEmptyThenShouldNotReturnProduct(): void
    {
        $this->productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getProduct')->willReturn($this->productExisting);
        $draft->method('getType')->willReturn(ExistingProductDraft::TYPE);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertArrayNotHasKey('product', $array);
    }

    public function testNormalizeWhenContextHasProductIncludedThenShouldReturnNormalizedProduct(): void
    {
        $formName = 'form-xxx';
        $this->formProvider->method('getForm')->willReturn($formName);

        $this->productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getProduct')->willReturn($this->productExisting);
        $draft->method('getType')->willReturn(ExistingProductDraft::TYPE);

        $array = $this->productDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
        $this->assertSame($formName, $array['product']['meta']['form']);
    }

    public function testNormalizeWhenNoProductToCompare(): void
    {
        $this->creator = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creator->method('getObjectToCompare')->willReturn(null);

        $this->productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getType')->willReturn(ExistingProductDraft::TYPE);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertIsArray($array);
        $this->assertArrayNotHasKey('label', $array);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $normalizer = $this->getProductDraftNormalizerInstance();
        $result = $normalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [$this->createMock(ProductDraftInterface::class), true],
            [$this->createMock(Attribute::class), false],
        ];
    }

    private function getProductDraftNormalizerInstance(): ProductDraftNormalizer
    {
        $normalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->formProvider,
            $this->productNormalizer
        );
        $normalizer->setProductFromDraftCreator($this->creator);
        $normalizer->setAttributeChangeService($this->attributeChangeService);
        $normalizer->setValuesNormalizer($this->valuesNormalizer);
        $normalizer->setDatetimePresenter($this->createMock(PresenterInterface::class));

        return $normalizer;
    }
}