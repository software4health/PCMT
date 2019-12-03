<?php

declare(strict_types=1);

namespace PcmtProductBundle\Command\Interfaces;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface PcmtConsecutiveJobExecutionInterface
{
    public function handle(InputInterface $input, OutputInterface $output): void;
}