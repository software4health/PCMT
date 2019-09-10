<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Command\Handler;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtReferenceDataDownloadHandler extends ContainerAwareCommand
{
    protected const DEFAULT_JOB_CODE = 'reference_data_download_xmls';

    protected static $defaultName = 'pcmt:handler:download_reference_data';

    public function configure()
    {
        $this->addArgument('code', InputArgument::OPTIONAL, 'Can override default job code name.');
        $this->addArgument('dirPath', InputArgument::OPTIONAL, 'Can override default directory where xmls are saved.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            $command = $this->getApplication()->find('akeneo:batch:job');
            $arguments = [
                'code' => ($input->getArgument('code')) ?? self::DEFAULT_JOB_CODE,
            ];
            if($dirPath = $input->getArgument('dirPath') !== null){
                $arguments['-c'] = $dirPath;
            }

            $input = new ArrayInput($arguments);

            $command->run($input, $output);

        } catch (\Exception $exception){
            $output->writeln($exception->getMessage());
            die;
        }
    }

}