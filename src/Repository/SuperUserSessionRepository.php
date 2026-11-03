<?php

namespace App\Repository;

use App\Entity\SuperUserSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuperUserSession>
 */
class SuperUserSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperUserSession::class);
    }

    public function findValidByTokenHash(string $tokenHash): ?SuperUserSession
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.tokenHash = :tokenHash')
            ->andWhere('s.expiresAt > :now')
            ->setParameter('tokenHash', $tokenHash)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
