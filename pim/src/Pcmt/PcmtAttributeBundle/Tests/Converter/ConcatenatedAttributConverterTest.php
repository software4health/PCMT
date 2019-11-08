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

    public function testShouldReturnValidConvertedArray()
    {
        $attribute = new Attribute();
        $data = [];
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $attribute->setCode('concatenated_test');
        $concatenatedAttributesConverter = $this->getConcatenatedAttributesConverterInstance();

        $this->assertTrue(array_key_exists('concatenated_test', $concatenatedAttributesConverter->convert($attribute->getCode(),$data)));
    }

    private function getConcatenatedAttributesConverterInstance(): ConcatenatedAttributesConverter
    {
        return new ConcatenatedAttributesConverter(
            $this->columnsResolverMock,
            $this->supportedAttributeTypes
        );
    }
}