<?php

namespace App\Repository;

use App\Entity\Abonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Abonnement>
 */
class AbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonnement::class);
    }

    /**
     * @return Abonnement[]
     */
    public function findActifs(\DateTimeInterface $at = new \DateTimeImmutable()): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.datedebut <= :at')
            ->andWhere('a.datefin >= :at')
            ->setParameter('at', $at)
            ->orderBy('a.datedebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
