<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

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
        // in following command we use already imported xmls stored in code repository
        $this->commandExecutor->runCommand('pcmt:handler:import_reference_data');

        return $this;
    }
}