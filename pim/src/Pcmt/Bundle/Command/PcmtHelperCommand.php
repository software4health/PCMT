<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Pcmt\Bundle\Entity\AdditionalTradeItemClassificationCodeListCode;
use Pcmt\Bundle\Entity\PackageTypeCode;
use Pcmt\Bundle\PcmtConnectorBundle\Command\Handler\ConsecutivePcmtJobExecutionHandler;
use Pcmt\Bundle\PcmtConnectorBundle\Command\Handler\PcmtReferenceDataDownloadHandler;
use Pcmt\Bundle\PcmtConnectorBundle\Exception\InvalidJobConfigurationException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pcmt\Bundle\PcmtConnectorBundle\Command\Handler\PcmtReferenceDataImportHandler;


class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    protected static $defaultName = 'pcmt:command';


    protected const CONSECUTIVE_JOBS= [
        1 => [
            'connector' => 'Pcmt Connector',
            'job' => 'reference_data_download_xmls',
            'code' => 'reference_data_download_xmls',
            'type' => 'data_download',
            'job_execution_handler' => 'pcmt:handler:download_reference_data'
        ],
        2 => [
            'connector' => 'Pcmt Connector',
            'job' => 'reference_data_import_xml',
            'code' => 'reference_data_import_xml',
            'type' => 'import',
            'config' => '{"dirPath": "%s"}',
            'job_execution_handler' => ''
        ]
    ];


    public function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * first check for download job instance, and if not there, create it
         */
       $trialCount = 4; //set max trial to create command
        try{
            $this->checkIfJobsExist($output, $trialCount);
        } catch (InvalidJobConfigurationException $exception){
            $output->writeln($exception->getMessage());
            die;
        }
        /**
         * then fire up commands consecutively
         */
        $this>$this->executeJobs($input, $output);
    }

    protected function executeJobs(InputInterface $input, OutputInterface $output) // put the jobs into one queue and execute them consecutively
    {
        $executionHandler = new ConsecutivePcmtJobExecutionHandler();
        $executionHandler->executeJobs($output);


        /*foreach (self::CONSECUTIVE_JOBS as $order => $job){
            $handler =  $this->getApplication()->find($job['handler']);
            $output->writeln(sprintf('Executing step:  %s', $job['job_execution_handler']));

            $arguments = [
                'code' => ($job['code']) ?? null,
                'dirPath' => ($job['dirPath']) ?? null
            ];

        }
        $executionHandler->executeJobs($input, $output);*/
    }


    private function checkIfJobsExist(OutputInterface $output, int $trialCount)
    {
        try{
            foreach (self::CONSECUTIVE_JOBS as $key => $jobInstanceParameters){
                $jobInstanceClass = $this->getContainer()->getParameter('akeneo_batch.entity.job_instance.class');
                $jobInstance = $this->getEntityManager()->getRepository($jobInstanceClass)->findOneBy(['code' => $jobInstanceParameters['code']]);

                if(null === $jobInstance){
                    throw new \Exception('Job  ' . $jobInstanceParameters['code'] . ' undefinded.');
                }
                $output->writeln('Job instance : ' . $jobInstanceParameters['code'] . ' found.');
            }
            return true;
        } catch (\Exception $exception){
            $output->writeln($exception->getMessage());
            if($trialCount > 0){
                $trialCount --;
                $output->writeln('Trying to (re)create job instance: ' . $jobInstanceParameters['code']);
                if($this->createJobInstanceFromParameters($jobInstanceParameters, $output)){

                    $this->checkIfJobsExist($output, $trialCount);
                }
            } else {
                throw new InvalidJobConfigurationException('Unable to (re)create job instance: ' . $jobInstanceParameters['code'] . ' check config.');
            }
        }
    }
    protected function createJobInstanceFromParameters(array $parameters, OutputInterface $output)
    {
        $command = $this->getApplication()->find('akeneo:batch:create-job');

        $arguments = [
            'connector' => $parameters['connector'],
            'job' => $parameters['job'],
            'type' => $parameters['type'],
            'code' => $parameters['code'],
            'config' => $parameters['config'] ?? null,
        ];

        $input = new ArrayInput($arguments);

        if($returnCode = $command->run($input, $output) == 0){
            return true;
        }

        return false;
    }

    private function getGS1MockDataList(): array
    {
       $package1 = [ 'class' => AdditionalTradeItemClassificationCodeListCode::class ];
       $package1['code'] = ['102', '100', '112'];
       $package1['name'] = ['GXS', 'CCG', 'EANFIN'];
       $package1['definition'] = ['GXS Product Data Quality (Formerly UDEX LTD)',
           'CCG - Code system used in the GS1 Germany market',
           'EANFIN - Code system used in the GS1 Finland market'];
       $package1['version'] = [1, 2, 2];
       $package1['status'] = [1,1,1];

        $package2 = [ 'class' => PackageTypeCode::class ];
        $package2['code'] = ['1A1', '1B1', '1F1'];
        $package2['name'] = ['Drum, steel', 'Drum, aluminium', 'Container, flexible'];
        $package2['definition'] = [null, null, 'A packaging container of flexible construction.'];
        $package2['version'] = [1, 1, 1];
        $package2['status'] = [1,1,1];

        return ['normalize_count' => 3, 'data' => [$package1, $package2]];
    }

    private function createGS1EntitiesFromMockDataLists()
    {
        $em = $this->getEntityManager();
        $normalizedPackage = $this->getGS1MockDataList();

        $em->getConnection()->beginTransaction();
        try{
            foreach ($normalizedPackage['data'] as $package){
                for($counter = 0; $counter < $normalizedPackage['normalize_count']; $counter++){

                    $newReferenceCode = new $package['class'];
                    $newReferenceCode->setCode($package['code'][$counter]);
                    $newReferenceCode->setName($package['name'][$counter]);
                    $newReferenceCode->setDefinition($package['definition'][$counter]);
                    $newReferenceCode->setVersion($package['version'][$counter]);
                    $newReferenceCode->setStatus($package['status'][$counter]);

                    $em->persist($newReferenceCode);
                }
            }
            $em->flush();
            $em->getConnection()->commit();
        }catch (\Exception $exception) {
            $em->getConnection()->rollBack();
            throw $exception;
        }
    }
    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}