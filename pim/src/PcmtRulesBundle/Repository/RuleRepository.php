<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use PcmtRulesBundle\Entity\Rule;

class RuleRepository extends EntityRepository implements DatagridRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        return $this->createQueryBuilder('rule');
    }

    public function findOneByIdentifier(string $identifier): ?Rule
    {
        return $this->findOneBy([
            'uniqueId' => $identifier,
        ]);
    }
}