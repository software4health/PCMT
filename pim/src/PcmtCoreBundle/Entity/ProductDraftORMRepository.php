<?php

declare(strict_types=1);

namespace PcmtCoreBundle\Entity;

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