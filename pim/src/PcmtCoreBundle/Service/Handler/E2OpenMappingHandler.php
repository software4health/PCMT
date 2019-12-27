<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 *
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\Handler;

use Doctrine\ORM\EntityManagerInterface;
use PcmtCoreBundle\Repository\AttributeMappingRepository;

class E2OpenMappingHandler
{
    private const MAPPING_TYPE = ['E2Open'];

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AttributeMappingRepository */
    private $attributeMappingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AttributeMappingRepository $attributeMappingRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->attributeMappingRepository = $attributeMappingRepository;
    }

    public function createMapping(Attribute $mapp): void
    {
        $
    }
}


