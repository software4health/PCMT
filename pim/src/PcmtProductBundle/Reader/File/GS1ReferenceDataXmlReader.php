<?php

declare(strict_types=1);

namespace PcmtProductBundle\Reader\File;

class GS1ReferenceDataXmlReader extends ReferenceDataXmlReader
{
    protected const DELIMITER = '{}';
}