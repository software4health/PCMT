<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductDraftORMRepository extends EntityRepository implements DraftRepositoryInterface
{
    public function findById(): ProductAbstractDraft
    {
        throw new NotImplementedException('method not implemented');
    }

    public function createDatagridQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d');
        $qb->select('d.id')
            ->addSelect('d.created')
            ->addSelect('CONCAT(a.firstName, \' \', a.lastName) AS fullName')
            ->addSelect('a.firstName')
            ->addSelect('a.lastName')
            ->leftJoin('d.author', 'a');
        return $qb;
    }

    public function getUserDrafts(UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('d');
        $qb->where(
            $qb->expr()->eq('d.author', ':author')
        );

        $qb->setParameters([
            ':author' => $user
        ]);

        return $qb->getQuery()->getResult();
    }
}