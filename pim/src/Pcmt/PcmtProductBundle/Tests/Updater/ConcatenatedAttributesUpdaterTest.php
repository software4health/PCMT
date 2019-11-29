<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Test\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Updater\EntityWithValuesUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Pcmt\PcmtProductBundle\Entity\Attribute;
use Pcmt\PcmtProductBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Pcmt\PcmtProductBundle\Updater\ConcatenatedAttributesUpdater;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ConcatenatedAttributesUpdaterTest extends TestCase
{
    /** @var EntityWithValuesUpdater|Mock */
    private $entityWithValuesUpdater;

    /** @var NormalizerInterface |Mock */
    private $rawValuesStorageFormatNormalizer;

    protected function setUp(): void
    {
        $this->entityWithValuesUpdater = $this->createMock(
            EntityWithValuesUpdater::class
        );
        $this->rawValuesStorageFormatNormalizer = $this->createMock(
            Serializer::class
        );
    }

    /**
     * @dataProvider getValidProductDataToUpdate
     */
    public function testUpdate(object $mockProductEntityObject, array $parameters, array $normalized): void
    {
        $updater = $this->getConcatenatedAttributesUpdaterInstance();

        $valueMock = $this->createMock(MetricValue::class);

        $mockProductEntityObject->expects($this->exactly(2))
            ->method('hasAttribute')
            ->willReturn(true);

        $mockProductEntityObject->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($valueMock);

        $mockProductEntityObject->expects($this->any())
            ->method('getValuesForVariation')
            ->willReturn(new WriteValueCollection());

        $valueMock->expects($this->atLeastOnce())
            ->method('__toString')
            ->willReturn('attrValue');

        $this->rawValuesStorageFormatNormalizer->expects($this->any())
            ->method('normalize')
            ->willReturn($normalized);

        $this->entityWithValuesUpdater->expects($this->any())
            ->method('update')
            ->with($mockProductEntityObject, $normalized);

        $updater->update($mockProductEntityObject, $parameters);
    }

    /**
     * @dataProvider getInvalidProductDataToUpdate
     */
    public function testThrowsExceptionWhenInvalidData(object $object, array $parameters): void
    {
        $updater = $this->getConcatenatedAttributesUpdaterInstance();
        $this->expectException(\InvalidArgumentException::class);

        $updater->update($object, $parameters);
    }

    public function getValidProductDataToUpdate(): array
    {
        $memberAttribute1 = new Attribute();
        $memberAttribute1->setCode('Weight');

        $memberAttribute2 = new Attribute();
        $memberAttribute2->setCode('Height');

        $memberAttributes = [
            $memberAttribute1,
            $memberAttribute2,
        ];

        $concatenatedAttribute = new Attribute();
        $concatenatedAttribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $concatenatedAttribute->setCode('concatenated_attribute');
        $concatenatedAttribute->setProperty('attributes', implode(',', $memberAttributes));
        $concatenatedAttribute->setProperty('separators', '$$$');

        return [
            [
                $this->createMock(Product::class),
                [
                    'concatenatedAttribute'  => $concatenatedAttribute,
                    'memberAttributes'       => $memberAttributes,
                ],
                [
                    'concatenated_attribute' => [
                        'data' => [
                            'data'   => ['attrValue$$$attrValue'],
                            'locale' => null,
                            'scope'  => null,
                        ],
                    ],
                ],
            ],

            [
                $this->createMock(ProductModel::class),
                [
                    'concatenatedAttribute'  => $concatenatedAttribute,
                    'memberAttributes'       => $memberAttributes,
                ],
                [
                    'concatenated_attribute' => [
                        'data' => [
                            'data'   => ['attrValue$$$attrValue'],
                            'locale' => null,
                            'scope'  => null,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getInvalidProductDataToUpdate(): array
    {
        return [
            [
                new Product(),
                [
                    'invalidKeyNotconcatenatedAttribute' => new Attribute(),
                    'memberAttributes'                   => [],
                ],
            ],
            [
                new ProductModel(),
                [
                    'concatenatedAttribute' => 'not an attribute',
                    'memberAttributes'      => [],
                ],
            ],
            [
                $this->createMock(Product::class),
                [
                    'concatenatedAttibute' => new Attribute(),
                    'memberAttributes'     => [],
                ],
            ],
            [
                new Product(),
                [
                    'concatenatedAttibute'       => new Attribute(),
                    'invalidKeyMemberAttributes' => [],
                ],
            ],
        ];
    }

    private function getConcatenatedAttributesUpdaterInstance(): ConcatenatedAttributesUpdater
    {
        return new ConcatenatedAttributesUpdater(
            $this->entityWithValuesUpdater,
            $this->rawValuesStorageFormatNormalizer
        );
    }
}