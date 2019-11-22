<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Tests\Updater;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Pcmt\PcmtAttributeBundle\Entity\Attribute;
use Pcmt\PcmtAttributeBundle\Extension\PcmtAttributeManager;
use Pcmt\PcmtAttributeBundle\Updater\AttributeUpdater;
use Pcmt\PcmtTranslationBundle\Updater\TranslatableUpdater;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class AttributeUpdaterTest extends TestCase
{
    /** @var AttributeGroupRepositoryInterface|Mock */
    protected $attrGroupRepoMock;

    /** @var LocaleRepositoryInterface|Mock */
    protected $localeRepositoryMock;

    /** @var AttributeTypeRegistry|Mock */
    protected $registryMock;

    /** @var TranslatableUpdater|Mock */
    protected $translatableUpdaterMock;

    /** @var array|Mock */
    protected $propertiesMock;

    /** @var Attribute|Mock */
    protected $attribute;

    /** @var PcmtAttributeManager|Mock */
    protected $attributeManager;

    public function setUp(): void
    {
        $this->attrGroupRepoMock = $this->createMock(AttributeGroupRepositoryInterface::class);
        $this->localeRepositoryMock = $this->createMock(LocaleRepositoryInterface::class);
        $this->registryMock = $this->createMock(AttributeTypeRegistry::class);
        $this->translatableUpdaterMock = $this->createMock(TranslatableUpdater::class);
        $this->attribute = $this->createMock(Attribute::class);
        $this->attributeManager = $this->createMock(PcmtAttributeManager::class);
        $this->propertiesMock = [];
        parent::setUp();
    }

    private function getAttributeUpdaterInstance(): AttributeUpdater
    {
        return new AttributeUpdater(
            $this->attrGroupRepoMock,
            $this->localeRepositoryMock,
            $this->registryMock,
            $this->translatableUpdaterMock,
            $this->attributeManager,
            $this->propertiesMock
        );
    }
    private function getWrongAttributeClassType(): AttributeUpdater
    {
        return $this->getAttributeUpdaterInstance();
    }

    /**
     * @dataProvider dataWithRightDescriptions
     */
    public function testUpdateFunctionShouldInvokeValidateDataTypeMethodAndSetDataMethodWhenRightData($data)
    {
        $attributeUpdater = $this->getMockBuilder(AttributeUpdater::class)
      ->setMethods(['validateDataType', 'setData'])
      ->setConstructorArgs(array(
        $this->attrGroupRepoMock,
        $this->localeRepositoryMock,
        $this->registryMock,
        $this->translatableUpdaterMock,
        $this->attributeManager,
        $this->propertiesMock, ))
      ->getMock();
        $attributeUpdater->expects($this->atLeastOnce())->method('validateDataType');
        $attributeUpdater->expects($this->atLeastOnce())->method('setData');
        $attributeUpdater->update($this->attribute, $data);
    }

    /**
     * @dataProvider dataWithRightDescriptions
     */
    public function testUpdateFunctionShouldThrowExceptionWhenWrongAttributeClassType($data)
    {
        $attribute = $this->getWrongAttributeClassType();
        $attributeUpdater = $this->getAttributeUpdaterInstance();
        $this->expectException(InvalidObjectException::class);
        $attributeUpdater->update($attribute, $data);
    }

    /**
     * @dataProvider dataWithUnknownProperty
     */
    public function testUpdateFunctionShouldThrowUnknownPropertyExceptionWhenUnknownPropertyInData($data)
    {
        $attributeUpdater = $this->getAttributeUpdaterInstance();
        $this->expectException(UnknownPropertyException::class);
        $attributeUpdater->update($this->attribute, $data);
    }

    /**
     * @dataProvider dataWithInvalidPropertyType
     */
    public function testUpdateFunctionShouldThrowInvalidPropertyTypeExceptionWithWrongData($data)
    {
        $attributeUpdater = $this->getAttributeUpdaterInstance();
        $this->expectException(InvalidPropertyTypeException::class);
        $attributeUpdater->update($this->attribute, $data);
    }
    /**
     * @dataProvider dataWithRightDescriptions
     */
    public function testUpdateFunctionShouldUpdateDescriptionsViaSetDataFunctionWhenRightData($data)
    {
        $attributeUpdater = $this->getAttributeUpdaterInstance();
        $this->translatableUpdaterMock->expects($this->atLeastOnce())
      ->method('updateDescription');
        $attributeUpdater->update($this->attribute, $data);
    }

    public function dataWithRightDescriptions()
    {
        return [
      'single description' => [['descriptions' => ['en_US' => 'alo']], 'code' => 'test'],
      'single description with other data' => [['descriptions' => ['en_US' => 'alo']]],
      'multi description' => [['descriptions' => ['en_US' => 'alo', 'de' => 'lol']]],
      'multi description with other data' => [['descriptions' => ['en_US' => 'alo', 'de' => 'lol']], 'code' => 'test'],
      'empty array' => [['descriptions' => []]],
    ];
    }
    public function dataWithUnknownProperty()
    {
        return [
      'one of property is unknown' => [['descriptions' => ['en_US' => 'alo'], 'unknown_property' => 0]],
      'wrong property' => [['description' => ['en_US' => 'alo']]],
    ];
    }
    public function dataWithInvalidPropertyType()
    {
        return [
      'not an array' => [['descriptions' => 'en_US']],
      'not a scalar' => [['descriptions' => [[]]]],
      'one is not a scalar' => [['descriptions' => ['en_US' => 'alo', 'de' => []]]],
    ];
    }
}