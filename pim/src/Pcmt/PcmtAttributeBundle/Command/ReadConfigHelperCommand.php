<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\Command;

use Pcmt\PcmtConnectorBundle\Util\Adapter\FileGetContentsWrapper;
use Sabre\Xml\Service;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadConfigHelperCommand extends ContainerAwareCommand
{
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
                'code' => 'Sabre\Xml\Element\KeyValue', ];

            $output = $parser->parse($input);

            foreach ($output as $values) {
                if ('{}code' === !$values['name'] || !is_array($values['value'])) {
                    continue;
                }

                dump(strtoupper($values['value']['{}name'] . ': ') . $values['value']['{}name']);
            }
        } catch (\Throwable $exception) {
            dump($exception);
            die;
        }
    }
}