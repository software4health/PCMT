<?php
declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Tests\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Pcmt\PcmtAttributeBundle\Entity\Attribute;
use Pcmt\PcmtAttributeBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use Pcmt\PcmtAttributeBundle\Extension\Connector\ArrayConverter\StandardToFlat\Product\ConcatenatedAttributesConverter;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class ConcatenatedAttributesConverterTest extends TestCase
{
    /** @var AttributeColumnsResolver|Mock */
    protected $columnsResolverMock;

    /** @var array $supportedAttributeTypes */
    private $supportedAttributeTypes;

    public function setUp(): void
    {
        $this->supportedAttributeTypes = [PcmtAtributeTypes::CONCATENATED_FIELDS];
        $this->columnsResolverMock = $this->createMock(AttributeColumnsResolver::class);
    }

    /** @dataProvider provideDataToConvert */
    public function testShouldReturnValidConvertedArray(array $input, array $converted): void
    {
        $attribute = new Attribute();
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $attribute->setCode('concatenated_test');
        $concatenatedAttributesConverter = $this->getConcatenatedAttributesConverterInstance();

        $this->assertTrue($concatenatedAttributesConverter->supportsAttribute($attribute));
        $this->assertEquals($converted, $concatenatedAttributesConverter->convert($attribute->getCode(),$input));
    }

    public function provideDataToConvert(): array
    {
        return [
          [
              ['attribute1' => '100 EUR', 'separator' => ':', 'attribute2' => '0.250KG'],
              ['concatenated_test' => '100 EUR:0.250KG']
          ]
        ];
    }

    private function getConcatenatedAttributesConverterInstance(): ConcatenatedAttributesConverter
    {
        return new ConcatenatedAttributesConverter(
            $this->columnsResolverMock,
            $this->supportedAttributeTypes
        );
    }
}