<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtCoreBundle\Entity\AbstractDraft;
use PcmtCoreBundle\Entity\AttributeChange;
use PcmtCoreBundle\Entity\ExistingProductModelDraft;
use PcmtCoreBundle\Entity\NewProductModelDraft;
use PcmtCoreBundle\Normalizer\AttributeChangeNormalizer;
use PcmtCoreBundle\Normalizer\DraftStatusNormalizer;
use PcmtCoreBundle\Normalizer\ProductModelDraftNormalizer;
use PcmtCoreBundle\Service\AttributeChange\ProductModelAttributeChangeService;
use PcmtCoreBundle\Service\Draft\DraftStatusTranslatorService;
use PcmtCoreBundle\Service\Draft\ProductModelFromDraftCreator;
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

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
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
        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->productModelNormalizer
        );
        $this->productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $this->productModelDraftNormalizer->setProductModelAttributeChangeService($this->attributeChangeService);

        $draft = $this->createMock(ExistingProductModelDraft::class);

        $array = $this->productModelDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
    }
}