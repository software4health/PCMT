<?php

declare(strict_types=1);

namespace Pcmt\Bundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    protected static $defaultName = 'pcmt:command';

    public function configure(): void
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}