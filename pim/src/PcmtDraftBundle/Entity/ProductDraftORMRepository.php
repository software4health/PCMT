<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Intl\Exception\NotImplementedException;

class ProductDraftORMRepository extends EntityRepository implements DraftRepositoryInterface
{
    public function findById(): AbstractDraft
    {
        throw new NotImplementedException('method not implemented');
    }

    public function getUserDrafts(UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('d');
        $qb->where(
            $qb->expr()->eq('d.author', ':author')
        );

        $qb->setParameters([
            ':author' => $user,
        ]);

        return $qb->getQuery()->getResult();
    }
}