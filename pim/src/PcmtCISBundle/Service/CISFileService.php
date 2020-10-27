<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

class CISFileService
{
    private const DESTINATION = 'GS1Engine';
    private const MESSAGE_TYPE = 'GDSNSubscription';
    private const VERSION = '1.0';

    /** @var Filesystem */
    private $filesystem;

    /** @var CISFileUniqueIdentifierGenerator */
    private $uniqueIdentifierGenerator;

    /** @var string */
    private $path;

    /** @var string */
    private $source;

    public function __construct(
        Filesystem $filesystem,
        CISFileUniqueIdentifierGenerator $uniqueIdentifierGenerator,
        string $path,
        string $source
    ) {
        $this->filesystem = $filesystem;
        $this->uniqueIdentifierGenerator = $uniqueIdentifierGenerator;
        $this->source = $source;
        $this->path = $path;
    }

    public function createFile(): void
    {
        $this->prepareDirectory($this->path);

        $destination = self::DESTINATION;
        $messageType = self::MESSAGE_TYPE;
        $version = self::VERSION;

        $uniqueIdentifier = $this->uniqueIdentifierGenerator->generate();

        $this->filesystem->touch("{$this->path}work/{$this->source}_{$destination}_{$messageType}_{$version}_{$uniqueIdentifier}.txt");
    }

    private function prepareDirectory(string $path): void
    {
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->mkdir($path);
        }

        if (!$this->filesystem->exists($path . 'work/')) {
            $this->filesystem->mkdir($path . 'work/');
        }

        if (!$this->filesystem->exists($path . 'done/')) {
            $this->filesystem->mkdir($path . 'done/');
        }
    }
}