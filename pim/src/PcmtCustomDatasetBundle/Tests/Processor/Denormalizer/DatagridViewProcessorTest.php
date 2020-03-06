<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\Processor\Denormalizer;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Exception\MissingIdentifierException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepository;
use PcmtCustomDatasetBundle\Processor\Denormalizer\DatagridViewProcessor;
use PcmtCustomDatasetBundle\Tests\TestDataBuilder\DatagridViewBuilder;
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

    /** @var UserRepositoryInterface|MockObject */
    private $userRepositoryMock;

    /** @var SimpleFactoryInterface|MockObject */
    private $factoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(DatagridViewRepository::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $cvMock = $this->createMock(ConstraintViolationListInterface::class);
        $this->validatorMock->method('validate')->willReturn($cvMock);
        $this->objectDetacherMock = $this->createMock(ObjectDetacherInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->factoryMock = $this->createMock(SimpleFactoryInterface::class);
    }

    private function getDatagridViewProcessorInstance(): DatagridViewProcessor
    {
        $updater = new PcmtDatagridViewUpdater($this->userRepositoryMock);

        return new DatagridViewProcessor(
            $this->repositoryMock,
            $this->factoryMock,
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
        $result = $this->invokeMethodOnProcessor(
            'getItemIdentifier',
            [
                null,
                $item,
            ]
        );
        $this->assertSame($item['label'], $result);
    }

    public function dataGetItemIdentifier(): array
    {
        return [
            [
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
        $this->expectException(MissingIdentifierException::class);
        $this->invokeMethodOnProcessor(
            'getItemIdentifier',
            [
                null,
                $item,
            ]
        );
    }

    public function dataGetItemIdentifierThrowAnException(): array
    {
        return [
            'empty data'     => [
                [],
            ],
            'not empty data' => [
                [
                    'xxx' => '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataFindOneByIdentifier
     */
    public function testFindOneByIdentifier(string $itemIdentifier, ?object $expectedResult): void
    {
        $this->repositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'type'  => DatagridView::TYPE_PUBLIC,
                    'label' => $itemIdentifier,
                ]
            )
            ->willReturn($expectedResult);
        $result = $this->invokeMethodOnProcessor('findOneByIdentifier', [$itemIdentifier]);
        $this->assertSame($expectedResult, $result);
    }

    public function dataFindOneByIdentifier(): array
    {
        return [
            'empty identifier'     => [
                '',
                null,
            ],
            'not empty identifier' => [
                'not empty identifier',
                (new DatagridViewBuilder())->build(),
            ],
        ];
    }

    /**
     * @dataProvider dataCreateObjectShouldCallFactory
     */
    public function testCreateObjectShouldCallFactory(string $itemIdentifier, ?StepExecution $stepExecution): void
    {
        $this->factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn((new DatagridViewBuilder())->build());
        $this->invokeMethodOnProcessor('createObject', [$itemIdentifier], $stepExecution);
    }

    public function dataCreateObjectShouldCallFactory(): array
    {
        $stepExecutionMock = $this->createMock(StepExecution::class);
        $executionContextMock = $this->createMock(ExecutionContext::class);
        $executionContextMock
            ->expects($this->at(0))
            ->method('get')
            ->with('processed_items_batch')
            ->willReturn(null);
        $executionContextMock
            ->expects($this->at(1))
            ->method('get')
            ->with('processed_items_batch')
            ->willReturn([]);
        $stepExecutionMock
            ->method('getExecutionContext')
            ->willReturn($executionContextMock);

        return [
            'empty identifier'                                    => [
                '',
                null,
            ],
            'not empty identifier and step execution is null'     => [
                'not empty identifier',
                null,
            ],
            'not empty identifier and execution context is empty null' => [
                'not empty identifier',
                $stepExecutionMock,
            ],
            'not empty identifier and execution context is empty array' => [
                'not empty identifier',
                $stepExecutionMock,
            ],
        ];
    }

    /**
     * @return array|string|object|null
     */
    private function invokeMethodOnProcessor(
        string $methodName,
        ?array $parameters = null,
        ?StepExecution $stepExecution = null
    ) {
        $datagridViewProcessor = $this->getDatagridViewProcessorInstance();
        if (isset($stepExecution)) {
            $datagridViewProcessor->setStepExecution($stepExecution);
        }
        $method = $this->getMethodByReflection($datagridViewProcessor, $methodName);

        return $method->invoke($datagridViewProcessor, ...$parameters);
    }

    private function getMethodByReflection(object $object, string $methodName): \ReflectionMethod
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}