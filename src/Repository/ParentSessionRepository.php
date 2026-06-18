<?php

namespace App\Repository;

use App\Entity\ParentSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParentSession>
 */
class ParentSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParentSession::class);
    }

    public function findValidByTokenHash(string $tokenHash): ?ParentSession
    {
        return $this->createQueryBuilder('s')
            ->addSelect('p')
            ->innerJoin('s.parent', 'p')
            ->andWhere('s.tokenHash = :tokenHash')
            ->andWhere('s.expiresAt > :now')
            ->andWhere('p.supp = false')
            ->setParameter('tokenHash', $tokenHash)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
