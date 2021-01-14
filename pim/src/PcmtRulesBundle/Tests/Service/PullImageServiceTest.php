<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use GuzzleHttp\Client;
use PcmtRulesBundle\Service\PullImageService;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ValueBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PullImageServiceTest extends TestCase
{
    /** @var string */
    private $tmpStorageDirMock = '/tmp/storage/test';

    /** @var FileStorer|MockObject */
    private $fileStorerMock;

    /** @var string */
    private $sourceAttributeCode = 'source_code';

    /** @var Client|MockObject */
    private $httpClientMock;

    /** @var StepExecution|MockObject */
    private $stepExecutionMock;

    protected function setUp(): void
    {
        $this->fileStorerMock = $this->createMock(FileStorer::class);
        $this->httpClientMock = $this->createMock(Client::class);
        $this->stepExecutionMock = $this->createMock(StepExecution::class);
    }

    public function dataProcessEntity(): array
    {
        $value = (new ValueBuilder())->withAttributeCode($this->sourceAttributeCode)->withData('xxx')->build();
        $product = (new ProductBuilder())->addValue($value)->build();
        $productNoValue = (new ProductBuilder())->build();

        return [
            [$productNoValue, 0],
            [$product, 1],
        ];
    }

    /** @dataProvider dataProcessEntity */
    public function testProcessEntity(EntityWithValuesInterface $entity, int $expectedStoreCalls): void
    {
        $this->fileStorerMock->expects($this->exactly($expectedStoreCalls))->method('store');
        $service = $this->getPullImageService();
        $service->processEntity($entity);
    }

    private function getPullImageService(): PullImageService
    {
        $service = new PullImageService($this->tmpStorageDirMock, $this->fileStorerMock);
        $service->setSourceAttributeCode($this->sourceAttributeCode);
        $service->setStepExecution($this->stepExecutionMock);
        $service->setHttpClient($this->httpClientMock);

        return $service;
    }
}