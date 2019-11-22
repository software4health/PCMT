<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Pcmt\PcmtProductBundle\Service\AttributeChangesService;
use PHPUnit\Framework\TestCase;

class AttributeChangesServiceTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $productNew;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $productExisting;

    public function setUp(): void
    {
        $this->productNew = $this->createMock(Product::class);
        $this->productExisting = $this->createMock(Product::class);
        parent::setUp();
    }

    public function testGetEmpty(): void
    {
        $service = new AttributeChangesService();
        $this->productNew->method('getValues')->willReturn(new WriteValueCollection());
        $changes = $service->get($this->productNew, null);
        $this->assertEmpty($changes);
    }

    public function testGetNotEmpty(): void
    {
        $service = new AttributeChangesService();
        $collection = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value');
        $collection->add($value);
        $this->productNew->method('getValues')->willReturn($collection);
        $changes = $service->get($this->productNew, null);
        $this->assertNotEmpty($changes);
        $this->assertCount(1, $changes);
    }

    public function testGetTwoSameProducts(): void
    {
        $service = new AttributeChangesService();
        $collection = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value');
        $collection->add($value);
        $this->productNew->method('getValues')->willReturn($collection);
        $this->productExisting->method('getValues')->willReturn($collection);
        $changes = $service->get($this->productNew, $this->productExisting);
        $this->assertEmpty($changes);
    }

    public function testGetTwoDifferentProducts(): void
    {
        $service = new AttributeChangesService();
        $collection = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value');
        $collection->add($value);
        $this->productNew->method('getValues')->willReturn($collection);
        $this->productNew->method('getIdentifier')->willReturn('ident');

        $collection2 = new WriteValueCollection();
        $value = ScalarValue::value('code', 'value2');
        $collection2->add($value);
        $this->productExisting->method('getValues')->willReturn($collection2);
        $changes = $service->get($this->productNew, $this->productExisting);
        $this->assertNotEmpty($changes);
        $this->assertCount(2, $changes);
    }
}