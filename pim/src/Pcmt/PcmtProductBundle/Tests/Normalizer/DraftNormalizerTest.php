<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\User;
use Pcmt\PcmtProductBundle\Entity\AbstractProductDraft;
use Pcmt\PcmtProductBundle\Entity\AttributeChange;
use Pcmt\PcmtProductBundle\Entity\NewProductDraft;
use Pcmt\PcmtProductBundle\Entity\PendingProductDraft;
use Pcmt\PcmtProductBundle\Normalizer\AttributeChangeNormalizer;
use Pcmt\PcmtProductBundle\Normalizer\DraftNormalizer;
use Pcmt\PcmtProductBundle\Normalizer\DraftStatusNormalizer;
use Pcmt\PcmtProductBundle\Service\AttributeChangesService;
use Pcmt\PcmtProductBundle\Service\DraftStatusTranslatorService;
use Pcmt\PcmtProductBundle\Service\ProductFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DraftNormalizerTest extends TestCase
{
    /** @var DraftNormalizer */
    private $draftNormalizer;

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

    /** @var AttributeChangesService */
    private $attributeChangesService;

    public function setUp(): void
    {
        $this->productNew = $this->createMock(Product::class);
        $this->productExisting = $this->createMock(Product::class);

        $this->attributeChangeNormalizer = new AttributeChangeNormalizer();
        $logger = $this->createMock(LoggerInterface::class);
        $translator = $this->createMock(DraftStatusTranslatorService::class);
        $this->draftStatusNormalizer = new DraftStatusNormalizer($logger, $translator);

        $this->creator = $this->createMock(ProductFromDraftCreator::class);
        $this->creator->method('getProductToCompare')->willReturn($this->productNew);

        $this->attributeChangesService = $this->createMock(AttributeChangesService::class);

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct()
    {
        $this->draftNormalizer = new DraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->creator,
            $this->attributeChangesService
        );

        $draft = $this->createMock(NewProductDraft::class);
        $author = new User();
        $author->setFirstName('Alfred');
        $author->setLastName('Nobel');
        $draft->method('getAuthor')->willReturn($author);
        $draft->method('getProduct')->willReturn(null);
        $draft->method('getStatus')->willReturn(AbstractProductDraft::STATUS_NEW);

        $array = $this->draftNormalizer->normalize($draft);

        $this->assertEmpty($array['changes']);
        $this->assertEquals('Alfred Nobel', $array['author']);
        $this->assertIsArray($array['status']);
        $this->assertArrayHasKey('id', $array['status']);
        $this->assertArrayHasKey('name', $array['status']);
    }

    public function testNormalizeChangesNewProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->attributeChangesService->method('get')->willReturn($changes);

        $this->draftNormalizer = new DraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->creator,
            $this->attributeChangesService
        );
        $draft = $this->createMock(NewProductDraft::class);
        $draft->method('getProduct')->willReturn(null);

        $array = $this->draftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeChangesExistingProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->attributeChangesService->method('get')->willReturn($changes);

        $this->draftNormalizer = new DraftNormalizer(
            $this->draftStatusNormalizer,
            $this->attributeChangeNormalizer,
            $this->creator,
            $this->attributeChangesService
        );

        $draft = $this->createMock(PendingProductDraft::class);
        $draft->method('getProduct')->willReturn($this->productExisting);

        $array = $this->draftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }
}