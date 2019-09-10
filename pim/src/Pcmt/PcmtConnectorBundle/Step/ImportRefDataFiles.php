<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Step;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Pcmt\PcmtConnectorBundle\Validator\Directory\DirectoryPathValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// step is used to import .xml files and save them to the directory
class ImportRefDataFiles extends AbstractStep
{
    /** @var string $directory */
    protected $directory;

    /** @var ClientInterface $guzzleClient */
    protected $guzzleClient;

    public function __construct($name, EventDispatcherInterface $eventDispatcher, JobRepositoryInterface $jobRepository)
    {
        $this->guzzleClient = new Client();
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    protected function doExecute(StepExecution $stepExecution)
    {
        $jobParameters = $stepExecution->getJobParameters();
        $urls =$jobParameters->get('xml_data_pick_urls');
        $dirPath = $jobParameters->get('dirPath');
        if($dirPath){
            $this->directory = $dirPath;
        }
        $directoryValidator = new DirectoryPathValidator();
        $directoryValidator->validate('reference_data_files_path', $this->directory);

        if(!is_dir($this->directory)){
            mkdir($this->directory, 0755);
        }

        foreach ($urls as $url){
            try{
                $path = $this->createFileNameForReferenceData($url);
                $filePath = fopen($path, 'w');
                sleep(1);
                $response = $this->guzzleClient->get($url, ['save_to' => $filePath]);
                $stepExecution->addSummaryInfo('Succesful parse', 'url: ' . $url . ' code: ' .$response->getStatusCode());
                fclose($filePath);
            } catch (\Exception $exception){
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
}