<?php
declare(strict_types=1);

namespace Pcmt\Bundle\PcmtConnectorBundle\Command\Handler;

use Pcmt\Bundle\PcmtConnectorBundle\Command\Interfaces\PcmtConsecutiveJobExecutionInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsecutivePcmtJobExecutionHandler extends ContainerAwareCommand
{
    /* protected $handlers;

    protected static $defaultName = 'pcmt:handler:batch_import';

    public function __construct(array $jobConfig)
    {
        $this->handlers = new ArrayCollection();
        parent::__construct();
    }

    public function executeJobs(InputInterface $input, OutputInterface $output): void
    {
        foreach ($this->handlers as $handler){
            $handler->executeJobs($input, $output);
        }
    }

    public function addHandler(PcmtConsecutiveJobExecutionInterface $handler): void
    {
        if(!$this->handlers->contains($handler)){
            $this->handlers->add($handler);
        }
    }

    public function getOrder(): int
    {
        return PcmtConsecutiveJobExecutionInterface::ORDER_START;
    }*/
}