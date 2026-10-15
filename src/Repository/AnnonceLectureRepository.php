<?php

namespace App\Repository;

use App\Entity\Annonce;
use App\Entity\AnnonceLecture;
use App\Entity\ParentEleve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnnonceLecture>
 */
class AnnonceLectureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnnonceLecture::class);
    }

    public function markAsRead(Annonce $annonce, ParentEleve $parent): void
    {
        $existing = $this->findOneBy([
            'annonce' => $annonce,
            'parent' => $parent,
        ]);

        if ($existing !== null) {
            return;
        }

        $lecture = (new AnnonceLecture())
            ->setAnnonce($annonce)
            ->setParent($parent)
            ->setReadAt(new \DateTimeImmutable());

        $em = $this->getEntityManager();
        $em->persist($lecture);
        $em->flush();
    }
}
