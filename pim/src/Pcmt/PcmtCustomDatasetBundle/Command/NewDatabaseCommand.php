<?php
declare(strict_types=1);

namespace Pcmt\PcmtCustomDatasetBundle\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Command\DatabaseCommand;

class NewDatabaseCommand extends DatabaseCommand
{

  /**
   * Launches all commands needed after fixtures loading
   *
   * @throws \Exception
   */
  protected function launchCommands(): NewDatabaseCommand
  {
    parent::launchCommands();
    if ($this->getContainer()->getParameter('installer_data') === "PcmtCustomDatasetBundle:pcmt_global") {
      $this->commandExecutor->runCommand('pcmt:custom-dataset:create');
    }
    return $this;
  }
}