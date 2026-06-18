<?php

namespace App\Repository;

use App\Entity\AbsenceEleve;
use App\Entity\Eleve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbsenceEleve>
 */
class AbsenceEleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbsenceEleve::class);
    }

    /**
     * @return AbsenceEleve[]
     */
    public function findByFilters(
        ?string $eleve = null,
        ?string $classe = null,
        ?string $section = null,
        ?string $anneescolaire = null,
        ?string $statut = null,
        ?\DateTimeInterface $dateDebut = null,
        ?\DateTimeInterface $dateFin = null,
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.dateAbsence', 'DESC');

        if ($eleve !== null) {
            $qb->andWhere('a.eleve LIKE :eleve')
                ->setParameter('eleve', '%'.$eleve.'%');
        }

        if ($classe !== null) {
            $qb->andWhere('a.classe LIKE :classe')
                ->setParameter('classe', '%'.$classe.'%');
        }

        if ($section !== null) {
            $qb->andWhere('a.section LIKE :section')
                ->setParameter('section', '%'.$section.'%');
        }

        if ($anneescolaire !== null) {
            $qb->andWhere('a.anneescolaire = :anneescolaire')
                ->setParameter('anneescolaire', $anneescolaire);
        }

        if ($statut !== null) {
            $qb->andWhere('a.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($dateDebut !== null) {
            $qb->andWhere('a.dateAbsence >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        }

        if ($dateFin !== null) {
            $qb->andWhere('a.dateAbsence <= :dateFin')
                ->setParameter('dateFin', $dateFin);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return AbsenceEleve[]
     */
    public function findByEleve(Eleve $eleve): array
    {
        $labels = $eleve->getPossibleAbsenceLabels();

        if ($labels === []) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.dateAbsence', 'DESC');

        $orX = $qb->expr()->orX();
        foreach ($labels as $index => $label) {
            $param = 'label'.$index;
            $orX->add($qb->expr()->eq('a.eleve', ':'.$param));
            $qb->setParameter($param, $label);
        }

        $qb->andWhere($orX);

        return $qb->getQuery()->getResult();
    }
}
