<?php

namespace App\Repository;

use App\Entity\ParentEleve;
use App\Entity\ParentPin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParentPin>
 */
class ParentPinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParentPin::class);
    }

    public function findOneByParent(ParentEleve $parent): ?ParentPin
    {
        return $this->findOneBy(['parent' => $parent]);
    }
}
