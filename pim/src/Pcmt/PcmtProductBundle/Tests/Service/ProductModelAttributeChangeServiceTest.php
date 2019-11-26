<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Pcmt\PcmtProductBundle\Service\ProductModelAttributeChangeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductModelAttributeChangeServiceTest extends TestCase
{
    /** @var MockObject|ProductModel */
    private $productModelNew;

    /** @var MockObject|ProductModel */
    private $productModelExisting;

    protected function setUp(): void
    {
        $this->productModelNew = $this->createMock(ProductModel::class);
        $this->productModelExisting = $this->createMock(ProductModel::class);
        parent::setUp();
    }

    public function testGetEmpty(): void
    {
        $service = new ProductModelAttributeChangeService();
        $this->productModelNew->method('getValues')->willReturn(new WriteValueCollection());
        $changes = $service->get($this->productModelNew, null);
        $this->assertEmpty($changes);
    }

    public function testGetNotEmpty(): void
    {
        $service = new ProductModelAttributeChangeService();
        $collection = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value');
        $collection->add($value);
        $this->productModelNew->method('getValues')->willReturn($collection);
        $changes = $service->get($this->productModelNew, null);
        $this->assertNotEmpty($changes);
        $this->assertCount(1, $changes);
    }

    public function testGetTwoSameProducts(): void
    {
        $service = new ProductModelAttributeChangeService();
        $collection = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value');
        $collection->add($value);
        $this->productModelNew->method('getValues')->willReturn($collection);
        $this->productModelExisting->method('getValues')->willReturn($collection);
        $changes = $service->get($this->productModelNew, $this->productModelExisting);
        $this->assertEmpty($changes);
    }

    public function testGetTwoDifferentProducts(): void
    {
        $service = new ProductModelAttributeChangeService();
        $collection = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value');
        $collection->add($value);
        $this->productModelNew->method('getValues')->willReturn($collection);
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $familyVariant->method('getCode')->willReturn('fvcode');
        $this->productModelNew->method('getFamilyVariant')->willReturn($familyVariant);

        $collection2 = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value2');
        $collection2->add($value);
        $this->productModelExisting->method('getValues')->willReturn($collection2);
        $changes = $service->get($this->productModelNew, $this->productModelExisting);
        $this->assertNotEmpty($changes);
        $this->assertCount(2, $changes);
    }
}