<?php

declare(strict_types=1);

namespace PcmtProductBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtProductBundle\Entity\AbstractDraft;
use PcmtProductBundle\Entity\AttributeChange;
use PcmtProductBundle\Entity\ExistingProductDraft;
use PcmtProductBundle\Entity\NewProductDraft;
use PcmtProductBundle\Normalizer\AttributeChangeNormalizer;
use PcmtProductBundle\Normalizer\DraftStatusNormalizer;
use PcmtProductBundle\Normalizer\ProductDraftNormalizer;
use PcmtProductBundle\Service\AttributeChange\ProductAttributeChangeService;
use PcmtProductBundle\Service\Draft\DraftStatusTranslatorService;
use PcmtProductBundle\Service\Draft\ProductFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $this->productDraftNormalizer = new ProductDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer
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
            $this->attributeChangeNormalizer
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
            $this->attributeChangeNormalizer
        );
        $this->productDraftNormalizer->setProductFromDraftCreator($this->creator);
        $this->productDraftNormalizer->setProductAttributeChangeService($this->productAttributeChangeService);

        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getProduct')->willReturn($this->productExisting);

        $array = $this->productDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }
}