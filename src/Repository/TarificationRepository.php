<?php

namespace App\Repository;

use App\Entity\Tarification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tarification>
 */
class TarificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarification::class);
    }

    /**
     * @return Tarification[]
     */
    public function findByNom(string $nom): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.nom LIKE :nom')
            ->setParameter('nom', '%'.$nom.'%')
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
