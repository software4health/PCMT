<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Reader\File;

class GS1ReferenceDataXmlReader extends ReferenceDataXmlReader
{
    protected const DELIMITER = '{}';
}