<?php

namespace App\Repository;

use App\Entity\Paiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    /**
     * @return Paiement[]
     */
    public function findByFilters(
        ?int $eleveId = null,
        ?int $tarificationId = null,
        ?string $mois = null,
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('e', 't')
            ->innerJoin('p.eleve', 'e')
            ->innerJoin('p.tarification', 't')
            ->orderBy('p.datepaiement', 'DESC');

        if ($eleveId !== null) {
            $qb->andWhere('e.id = :eleveId')
                ->setParameter('eleveId', $eleveId);
        }

        if ($tarificationId !== null) {
            $qb->andWhere('t.id = :tarificationId')
                ->setParameter('tarificationId', $tarificationId);
        }

        if ($mois !== null) {
            $qb->andWhere('p.mois = :mois')
                ->setParameter('mois', $mois);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Paiement[]
     */
    public function findByEleveAndAnneeScolaire(int $eleveId, int $anneescolaireId): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('e', 't')
            ->innerJoin('p.eleve', 'e')
            ->innerJoin('p.tarification', 't')
            ->andWhere('e.id = :eleveId')
            ->andWhere('p.anneescolaireId = :anneescolaireId')
            ->setParameter('eleveId', $eleveId)
            ->setParameter('anneescolaireId', $anneescolaireId)
            ->orderBy('p.datepaiement', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Paiement[]
     */
    public function findByEleveAndMois(int $eleveId, string $mois): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('e', 't')
            ->innerJoin('p.eleve', 'e')
            ->innerJoin('p.tarification', 't')
            ->andWhere('e.id = :eleveId')
            ->andWhere('p.mois = :mois')
            ->setParameter('eleveId', $eleveId)
            ->setParameter('mois', $mois)
            ->orderBy('p.datepaiement', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
