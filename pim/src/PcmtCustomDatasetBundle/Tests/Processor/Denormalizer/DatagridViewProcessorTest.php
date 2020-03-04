<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\Processor\Denormalizer;

use Akeneo\Tool\Component\Connector\Exception\MissingIdentifierException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepository;
use PcmtCustomDatasetBundle\Processor\Denormalizer\DatagridViewProcessor;
use PcmtCustomDatasetBundle\Updater\PcmtDatagridViewUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DatagridViewProcessorTest extends TestCase
{
    /** @var DatagridViewRepository|MockObject */
    private $repositoryMock;

    /** @var ValidatorInterface|MockObject */
    private $validatorMock;

    /** @var ObjectDetacherInterface|MockObject */
    private $objectDetacherMock;

    /** @var UserRepositoryInterface */
    private $userRepositoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(DatagridViewRepository::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $cvMock = $this->createMock(ConstraintViolationListInterface::class);
        $this->validatorMock->method('validate')->willReturn($cvMock);
        $this->objectDetacherMock = $this->createMock(ObjectDetacherInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
    }

    private function getDatagridViewProcessorInstance(): DatagridViewProcessor
    {
        $updater = new PcmtDatagridViewUpdater($this->userRepositoryMock);
        $datagridViewFactory = new SimpleFactory(DatagridView::class);

        return new DatagridViewProcessor(
            $this->repositoryMock,
            $datagridViewFactory,
            $updater,
            $this->validatorMock,
            $this->objectDetacherMock
        );
    }

    /**
     * @dataProvider dataGetItemIdentifier
     *
     * @throws \ReflectionException
     */
    public function testGetItemIdentifierIsALabel(array $item): void
    {
        $datagridViewProcessor = $this->getDatagridViewProcessorInstance();
        $method = $this->getMethodByReflection($datagridViewProcessor, 'getItemIdentifier');
        $result = $method->invoke($datagridViewProcessor, null, $item);
        $this->assertSame($item['label'], $result);
    }

    public function dataGetItemIdentifier(): array
    {
        return [
            '' => [
                [
                    'label' => 'I am the identifier',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetItemIdentifierThrowAnException
     *
     * @throws \ReflectionException
     */
    public function testGetItemIdentifierThrowAnException(array $item): void
    {
        $datagridViewProcessor = $this->getDatagridViewProcessorInstance();
        $method = $this->getMethodByReflection($datagridViewProcessor, 'getItemIdentifier');
        $this->expectException(MissingIdentifierException::class);
        $method->invoke($datagridViewProcessor, null, $item);
    }

    public function dataGetItemIdentifierThrowAnException(): array
    {
        return [
            'empty data' => [
                [],
            ],
            'not empty data' => [
                [
                    'xxx' => '',
                ],
            ],
        ];
    }

    private function getMethodByReflection(object $object, string $methodName): \ReflectionMethod
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}