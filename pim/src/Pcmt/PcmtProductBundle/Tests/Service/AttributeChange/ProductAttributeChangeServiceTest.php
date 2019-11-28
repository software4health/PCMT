<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Service\AttributeChange;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pcmt\PcmtProductBundle\Service\AttributeChange\ProductAttributeChangeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class ProductAttributeChangeServiceTest extends TestCase
{
    /** @var MockObject|ProductInterface */
    private $productNew;

    /** @var MockObject|ProductInterface */
    private $productExisting;

    /** @var MockObject|Serializer */
    private $versioningSerializer;

    protected function setUp(): void
    {
        $this->productNew = $this->createMock(Product::class);
        $this->productExisting = $this->createMock(Product::class);
        $this->versioningSerializer = $this->createMock(Serializer::class);
        parent::setUp();
    }

    public function testGetEmpty(): void
    {
        $this->versioningSerializer->method('normalize')->willReturn([]);
        $service = new ProductAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productNew, null);
        $this->assertEmpty($changes);
    }

    public function testGetNotEmpty(): void
    {
        $this->versioningSerializer->method('normalize')
            ->will($this->onConsecutiveCalls(['attribute1' => 'value1']));
        $service = new ProductAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productNew, null);
        $this->assertNotEmpty($changes);
        $this->assertCount(1, $changes);
    }

    public function testGetTwoSameProducts(): void
    {
        $this->versioningSerializer->method('normalize')
            ->will($this->onConsecutiveCalls(
                ['attribute1' => 'value1'],
                ['attribute1' => 'value1']
            ));
        $service = new ProductAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productNew, $this->productExisting);
        $this->assertEmpty($changes);
    }

    public function testGetTwoDifferentProducts(): void
    {
        $this->versioningSerializer->method('normalize')
            ->will($this->onConsecutiveCalls(
                [
                    'attribute1' => 'value1',
                    'attribute2' => 'value2',
                ],
                ['attribute1' => 'value3']
            ));

        $service = new ProductAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productNew, $this->productExisting);
        $this->assertNotEmpty($changes);
        $this->assertCount(2, $changes);
    }
}