<?php

declare(strict_types=1);

namespace PcmtProductBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\UserManagement\Component\Model\User;
use PcmtProductBundle\Entity\AbstractDraft;
use PcmtProductBundle\Entity\AttributeChange;
use PcmtProductBundle\Entity\ExistingProductModelDraft;
use PcmtProductBundle\Entity\NewProductModelDraft;
use PcmtProductBundle\Normalizer\AttributeChangeNormalizer;
use PcmtProductBundle\Normalizer\DraftStatusNormalizer;
use PcmtProductBundle\Normalizer\ProductModelDraftNormalizer;
use PcmtProductBundle\Service\AttributeChange\ProductModelAttributeChangeService;
use PcmtProductBundle\Service\Draft\DraftStatusTranslatorService;
use PcmtProductBundle\Service\Draft\ProductModelFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $this->productModelDraftNormalizer = new ProductModelDraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer
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
            $this->attributeChangeNormalizer
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
            $this->attributeChangeNormalizer
        );
        $this->productModelDraftNormalizer->setProductModelFromDraftCreator($this->creator);
        $this->productModelDraftNormalizer->setProductModelAttributeChangeService($this->attributeChangeService);

        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($this->productModelExisting);

        $array = $this->productModelDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }
}