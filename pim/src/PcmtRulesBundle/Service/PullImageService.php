<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class PullImageService
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var string */
    private $sourceAttributeCode;

    /** @var string */
    private $tmpStorageDir;

    /** @var FileStorer */
    private $fileStorer;

    /** @var Client */
    private $httpClient;

    public function __construct(
        string $tmpStorageDir,
        FileStorer $fileStorer
    ) {
        $this->tmpStorageDir = $tmpStorageDir;
        $this->fileStorer = $fileStorer;
        $this->httpClient = new Client();
    }

    public function setSourceAttributeCode(string $sourceAttributeCode): void
    {
        $this->sourceAttributeCode = $sourceAttributeCode;
    }

    public function processEntity(EntityWithValuesInterface $entity): ?FileInfoInterface
    {
        $sourceValue = $entity->getValue($this->sourceAttributeCode);
        if (!$sourceValue || !$sourceValue->getData()) {
            return null;
        }

        $this->stepExecution->incrementSummaryInfo('source_urls_found', 1);

        $url = $sourceValue->getData();
        $parts = explode('/', $url);
        $originalFilename = end($parts);
        $originalFilename = $originalFilename ?: 'name';
        $filePath = $this->tmpStorageDir . '/' . $originalFilename;

        $this->httpClient->get($url, [
            RequestOptions::SINK            => $filePath,
            RequestOptions::TIMEOUT         => 15,
            RequestOptions::CONNECT_TIMEOUT => 5,
        ]);

        $downloadedFile = new \SplFileInfo($filePath);

        return $this->fileStorer->store($downloadedFile, FileStorage::CATALOG_STORAGE_ALIAS, true);
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}