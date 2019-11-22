<?php

declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Reader\File;

class GS1ReferenceDataXmlReader extends ReferenceDataXmlReader
{
    protected const DELIMITER = '{}';
}