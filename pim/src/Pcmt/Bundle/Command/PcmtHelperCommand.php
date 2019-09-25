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

    public function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $registry = $this->getContainer()->get('pim_catalog.registry.attribute_type');
        $entries = $registry->getSortedAliases();
        dump($registry);
        $output->writeln('Entries: ');
        dump($entries);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}