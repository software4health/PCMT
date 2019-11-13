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

    /** @dataProvider provideValidDataToConvert */
    public function testShouldReturnValidConvertedArray(array $input, array $converted): void
    {
        $attribute = new Attribute();
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $attribute->setCode('concatenated_test');
        $concatenatedAttributesConverter = $this->getConcatenatedAttributesConverterInstance();

        $this->assertTrue($concatenatedAttributesConverter->supportsAttribute($attribute));
        $this->assertEquals($converted, $concatenatedAttributesConverter->convert($attribute->getCode(),$input));
    }

    /** @dataProvider provideInvalidDataToConvert */
    public function testShouldThrowExceptionIfInvalidDataFormat($input): void
    {
        $attribute = new Attribute();
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $attribute->setCode('concatenated_test');
        $concatenatedAttributesConverter = $this->getConcatenatedAttributesConverterInstance();

        $this->expectException(\InvalidArgumentException::class);
        $concatenatedAttributesConverter->convert($attribute->getCode(), $input);
    }

    public function provideValidDataToConvert(): array
    {
        return [
          [
              ['attribute1' => '100 EUR', 'separator' => ':', 'attribute2' => '0.250KG'],
              ['concatenated_test' => '100 EUR:0.250KG']
          ],
          [
              ['attribute1' => ['200 PSI', '|', '100 kG/m2'], 'separator' => '%%', 'attribute2' => '80 USD'],
              ['concatenated_test' => '200 PSI|100 kG/m2%%80 USD']
          ]
        ];
    }

    public function provideInvalidDataToConvert(): array
    {
        return [
            [
                ['invalidKey' => '200 EUR', 'separator' => '$$', 'attribute1' => '0.250KG']
            ],
            [
                'invalidValueType'
            ],
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