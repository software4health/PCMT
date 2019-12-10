<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtCoreBundle\Entity\AbstractDraft;
use PcmtCoreBundle\Entity\AttributeChange;
use PcmtCoreBundle\Entity\ExistingProductDraft;
use PcmtCoreBundle\Entity\NewProductDraft;
use PcmtCoreBundle\Normalizer\AttributeChangeNormalizer;
use PcmtCoreBundle\Normalizer\DraftStatusNormalizer;
use PcmtCoreBundle\Normalizer\ProductDraftNormalizer;
use PcmtCoreBundle\Service\AttributeChange\ProductAttributeChangeService;
use PcmtCoreBundle\Service\Draft\DraftStatusTranslatorService;
use PcmtCoreBundle\Service\Draft\ProductFromDraftCreator;
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

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
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
        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->productNormalizer
        );

        $this->productDraftNormalizer->setProductFromDraftCreator($this->creator);
        $this->productDraftNormalizer->setProductAttributeChangeService($this->productAttributeChangeService);

        $draft = $this->createMock(ExistingProductDraft::class);

        $array = $this->productDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
    }
}