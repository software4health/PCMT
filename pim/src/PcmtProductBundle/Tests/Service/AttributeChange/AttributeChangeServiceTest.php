<?php

declare(strict_types=1);

namespace PcmtProductBundle\Tests\Service\AttributeChange;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtProductBundle\Entity\Attribute;
use PcmtProductBundle\Entity\AttributeChange;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class AttributeChangeServiceTest extends TestCase
{
    /** @var MockObject|Serializer */
    private $versioningSerializerMock;

    /** @var MockObject|AttributeRepositoryInterface */
    protected $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->versioningSerializerMock = $this->createMock(Serializer::class);
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
        parent::setUp();
    }

    private function getAttributeChangeServiceInstance(): FakeAttributeChangeService
    {
        return new FakeAttributeChangeService(
            $this->versioningSerializerMock,
            $this->attributeRepositoryMock
        );
    }

    private function getAttributeName(AttributeChange $entity): string
    {
        return $entity->getAttributeName();
    }

    /**
     * @dataProvider dataForCreateChangeTest
     *
     * @throws \ReflectionException
     */
    public function testCreateChangeFunction(string $attributeCode, ?string $attributeLabel): void
    {
        $attributeChangeService = $this->getAttributeChangeServiceInstance();
        $attributeInstanceMock = null;
        if (null !== $attributeLabel) {
            $attributeInstanceMock = $this->createMock(Attribute::class);
            $attributeInstanceMock->method('getLabel')->willReturn($attributeLabel);
        } else {
            $attributeLabel = $attributeCode;
        }
        $this->attributeRepositoryMock->method('findOneByIdentifier')->willReturn($attributeInstanceMock);
        $attributeChangeService->createChange($attributeCode, 'zm1', 'zm2');
        $this->assertSame($attributeLabel, $this->getAttributeName($attributeChangeService->getChanges()[0]));
    }

    public function dataForCreateChangeTest(): array
    {
        return [
            'an attribute'     => [
                'attributeCode'   => 'attribute_1',
                'attributeLabel'  => 'Attribute 1',
            ],
            'not an attribute' => [
                'attributeCode'   => 'attribute_1',
                'attributeLabel'  => null,
            ],
        ];
    }
}