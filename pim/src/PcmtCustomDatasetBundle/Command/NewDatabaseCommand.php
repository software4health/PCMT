<?php

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Command\DatabaseCommand;

class NewDatabaseCommand extends DatabaseCommand
{
    /**
     * Launches all commands needed after fixtures loading
     *
     * @throws \Exception
     */
    protected function launchCommands(): self
    {
        parent::launchCommands();
        if ('PcmtCustomDatasetBundle:pcmt_global' === $this->getContainer()->getParameter('installer_data')) {
            $this->commandExecutor->runCommand('pcmt:custom-dataset:create');
        }

        return $this;
    }
}