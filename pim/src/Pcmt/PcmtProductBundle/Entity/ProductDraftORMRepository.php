<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;

class ProductDraftORMRepository extends EntityRepository implements DraftRepositoryInterface
{
    public function findById(): ProductAbstractDraft
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
            ':author' => $user
        ]);

        return $qb->getQuery()->getResult();
    }
}