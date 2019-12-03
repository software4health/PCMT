<?php

declare(strict_types=1);

namespace PcmtProductBundle\Command\Handler;

use PcmtProductBundle\Registry\PcmtConnectorJobParametersRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtReferenceDataImportHandler extends ContainerAwareCommand
{
    protected const DEFAULT_JOB_CODE = 'reference_data_import_xml';

    /** @var \RegexIterator */
    protected $fileIterator;

    /** @var string */
    protected $dir;

    /** @var string */
    protected static $defaultName = 'pcmt:handler:import_reference_data';

    public function __construct()
    {
        $this->dir = 'src/PcmtProductBundle/Resources/config/';
        $directory = new \RecursiveDirectoryIterator($this->dir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $this->fileIterator = new \RegexIterator($iterator, '/^.+\.xml$/i', \RecursiveRegexIterator::ALL_MATCHES);

        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('dirPath', InputArgument::OPTIONAL, 'Can override default directory where xmls are saved.');
        $this->addArgument('code', InputArgument::OPTIONAL, 'Can override default job code name.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $this->createJobIfNotExists($output);
            $this->fileIterator->rewind();
            while ($this->fileIterator->current()) {
                $currentFile = $this->fileIterator->key();
                $totalPath = str_replace('/', '\/', $currentFile);
                $arguments['code'] = $input->getArgument('code') ?? self::DEFAULT_JOB_CODE;
                $arguments['--config'] = sprintf('{"filePath": "%s"}', $totalPath);
                $returnCode = $this->executeCommand($output, $arguments);

                if (0 === $returnCode) {
                    $this->fileIterator->next();
                }
            }
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());
            die;
        }
    }

    private function executeCommand(OutputInterface $output, array $arguments): int
    {
        try {
            $command = $this->getApplication()->find('akeneo:batch:job');
            $input = new ArrayInput($arguments);

            return $command->run($input, $output);
        } catch (\Throwable $exception) {
            $output->writeln($exception);
            die;
        }
    }

    private function createJobIfNotExists(OutputInterface $output): void
    {
        $jobCreatror = $this->getApplication()->find('pcmt:job-creator');
        $arguments = [
            'jobName' => PcmtConnectorJobParametersRegistry::JOB_REFERENCE_DATA_IMPORT_NAME,
        ];
        $input = new ArrayInput($arguments);
        $jobCreatror->run($input, $output);
    }
}