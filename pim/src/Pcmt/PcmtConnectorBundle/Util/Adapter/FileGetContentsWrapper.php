<?php

declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Util\Adapter;

class FileGetContentsWrapper
{
    /**
     * @return false|string
     */
    public function fileGetContents(string $filename)
    {
        return file_get_contents($filename);
    }
}