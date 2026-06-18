<?php

namespace App\Repository;

use App\Entity\AnneeScolaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnneeScolaire>
 */
class AnneeScolaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnneeScolaire::class);
    }

    public function findEnCours(): ?AnneeScolaire
    {
        $enCours = $this->createQueryBuilder('a')
            ->andWhere('a.supp = false')
            ->andWhere('a.status = :status')
            ->setParameter('status', 'En Cours')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($enCours !== null) {
            return $enCours;
        }

        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('a')
            ->andWhere('a.supp = false')
            ->andWhere('a.dateDebut <= :today')
            ->andWhere('a.dateFin >= :today')
            ->setParameter('today', $today)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
