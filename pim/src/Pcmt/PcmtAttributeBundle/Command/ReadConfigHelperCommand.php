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

    public function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser = new Service();
        $fileGetContentsWrapper = new FileGetContentsWrapper();

        try{
            $filePath = __DIR__.'/MeasurementUnitCode_GDSN.xml';
            $input = $fileGetContentsWrapper->fileGetContents($filePath);

            $parser->elementMap = [
                    '{http://www.w3.org/2001/XMLSchema-instance}urn' => 'Sabre\Xml\Element\XmlElement',
                    'code' => 'Sabre\Xml\Element\KeyValue'];

            $output = $parser->parse($input);

            foreach ($output as $key => $values) {
                if (!$values['name'] === '{}code' || !is_array($values['value'])) {
                    continue;
                }

                dump(strtoupper($values['value']['{}name'] . ":"));
                dump("   symbol: " . $values['value']['{}value']);
            }
        } catch (\Exception $exception){
            dump($exception);
            die;
        }
    }
}