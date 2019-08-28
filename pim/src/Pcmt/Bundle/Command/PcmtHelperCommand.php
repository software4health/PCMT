<?php
declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pcmt\Bundle\Entity\AdditionalTradeItemClassificationCodeListCode;
use Pcmt\Bundle\Entity\PackageTypeCode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    protected static $defaultName = 'pcmt:command';

    public function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getEntityManager();
        try{

            $this->createGS1EntitiesFromMockDataLists();

            // find codes for PackageType reference Data
            $repo =  $em->getRepository(PackageTypeCode::class);

           if(!$repo instanceof ReferenceDataRepositoryInterface){
               $output->writeln('To work properly, Reference Data has to be bind to CustomEntityRepository');
               die;
           }

           $options = $repo->findBySearch(null, ['code']);

           foreach ($options as $option){
                var_dump($option);
            }

        }catch (\Exception $exception){
            $output->writeln($exception);
            die;
        }
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