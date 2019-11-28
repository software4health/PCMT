<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Tests\Service\AttributeChange;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Pcmt\PcmtProductBundle\Service\AttributeChange\ProductModelAttributeChangeService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class ProductModelAttributeChangeServiceTest extends TestCase
{
    /** @var MockObject|ProductModel */
    private $productModelNew;

    /** @var MockObject|ProductModel */
    private $productModelExisting;

    /** @var MockObject|Serializer */
    private $versioningSerializer;

    protected function setUp(): void
    {
        $this->productModelNew = $this->createMock(ProductModel::class);
        $this->productModelExisting = $this->createMock(ProductModel::class);
        $this->versioningSerializer = $this->createMock(Serializer::class);
        parent::setUp();
    }

    public function testGetEmpty(): void
    {
        $this->versioningSerializer->method('normalize')->willReturn([]);
        $service = new ProductModelAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productModelNew, null);
        $this->assertEmpty($changes);
    }

    public function testGetNotEmpty(): void
    {
        $this->versioningSerializer->method('normalize')
            ->will($this->onConsecutiveCalls(['attribute1' => 'value1']));
        $service = new ProductModelAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productModelNew, null);
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

        $service = new ProductModelAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productModelNew, $this->productModelExisting);
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

        $service = new ProductModelAttributeChangeService($this->versioningSerializer);
        $changes = $service->get($this->productModelNew, $this->productModelExisting);
        $this->assertNotEmpty($changes);
        $this->assertCount(2, $changes);
    }
}