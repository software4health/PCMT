<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PcmtHelperCommand extends ContainerAwareCommand
{
    /**
     * run inside terminal in fpm docker: bin/console pcmt:command
     */
    /** @var string */
    protected static $defaultName = 'pcmt:command';

    public function configure(): void
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $attributeRepository = $this->getContainer()->get('pim_catalog.repository.attribute');
        $mappingAttribute = $attributeRepository->findOneBy(
            [
                'code' => 'GTIN',
            ]
        );
        $mappedAttribute = $attributeRepository->findOneBy(
            [
                'code' => 'sku',
            ]
        );
        $handler = $this->getContainer()->get('pcmt_e2Open_mapping_handler');
        $handler->createMapping(
            $mappingAttribute,
            $mappedAttribute
        );
    }
}