<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command;

use PcmtCoreBundle\Util\Adapter\FileGetContentsWrapper;
use Sabre\Xml\Service;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadConfigHelperCommand extends ContainerAwareCommand
{
    /** @var string */
    protected static $defaultName = 'pcmt:measures';

    public function configure(): void
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $parser = new Service();
        $fileGetContentsWrapper = new FileGetContentsWrapper();

        try {
            $filePath = __DIR__.'/MeasurementUnitCode_GDSN.xml';
            $input = $fileGetContentsWrapper->fileGetContents($filePath);

            $parser->elementMap = [
                '{http://www.w3.org/2001/XMLSchema-instance}urn' => 'Sabre\Xml\Element\XmlElement',
                'code'                                           => 'Sabre\Xml\Element\KeyValue',
            ];

            $output = $parser->parse($input);

            foreach ($output as $values) {
                if ('{}code' === !$values['name'] || !is_array($values['value'])) {
                    continue;
                }
            }
        } catch (\Throwable $exception) {
        }
    }
}