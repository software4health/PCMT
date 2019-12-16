<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Connector\Job\Writer;

interface CrossJoinExportWriterInterface
{
    public function writeCross(array $items, array $crossItems): void;
}