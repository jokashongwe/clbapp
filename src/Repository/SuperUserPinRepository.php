<?php

namespace App\Repository;

use App\Entity\SuperUserPin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuperUserPin>
 */
class SuperUserPinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperUserPin::class);
    }

    public function findOneByTelephone(string $telephoneNational): ?SuperUserPin
    {
        return $this->findOneBy(['telephone' => $telephoneNational]);
    }
}
