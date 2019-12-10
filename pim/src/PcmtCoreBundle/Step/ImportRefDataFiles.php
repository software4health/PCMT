<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Step;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use PcmtCoreBundle\Util\Adapter\DirectoryCreator;
use PcmtCoreBundle\Validator\Directory\DirectoryPathValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// step is used to import .xml files and save them to the directory
class ImportRefDataFiles extends AbstractStep
{
    /** @var string */
    protected $directory;

    /** @var ClientInterface */
    protected $guzzleClient;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, EventDispatcherInterface $eventDispatcher, JobRepositoryInterface $jobRepository)
    {
        $this->guzzleClient = new Client();
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $jobParameters = $stepExecution->getJobParameters();
        $urls = $jobParameters->get('xml_data_pick_urls');
        $dirPath = $jobParameters->get('dirPath');
        if ($dirPath) {
            $this->directory = $dirPath;
        }
        $directoryValidator = new DirectoryPathValidator();
        $directoryValidator->validate('reference_data_files_path', $this->directory);

        $this->createDirectories($this->directory);
        DirectoryCreator::createDirectory($this->directory);

        foreach ($urls as $url) {
            try {
                $path = $this->createFileNameForReferenceData($url);
                $filePath = fopen($path, 'w');
                sleep(1);
                $response = $this->guzzleClient->get($url, ['save_to' => $filePath]);
                $stepExecution->addSummaryInfo('Succesful parse', 'url: ' . $url . ' code: ' .$response->getStatusCode());
                fclose($filePath);
            } catch (\Throwable $exception) {
                $stepExecution->addError('Failed to parse url: ' . $url . 'error: ' . $exception->getMessage());

                continue;
            }
        }
    }

    private function createFileNameForReferenceData(string $url): string
    {
        $matches = [];
        preg_match('/cl:(.*?)&/', $url, $matches) . '.xml';   // add validation and exception when unable to determine filename - throw exception
        $filename = $matches[1] . '.xml';

        return $this->directory . $filename;
    }

    private function createDirectories(string $path): bool
    {
        $path_split = explode('/', $path); //array
        $buildPath = '';
        foreach ($path_split as $pathElem) {
            if ('' === $pathElem) {
                continue;
            }
            $buildPath .= $pathElem . '/';
            if (is_dir($buildPath)) {
                continue;
            }
            mkdir($buildPath, 0777);
        }

        return true;
    }
}