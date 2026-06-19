<?php

namespace App\Repository;

use App\Entity\ParentEleve;
use App\Util\PhoneNormalizer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParentEleve>
 */
class ParentEleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParentEleve::class);
    }

  /**
     * @return int[]
     */
    public function findActiveIdsByTelephoneTuteur(string $normalizedTelephone): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT p.id
            FROM parent_eleve p
            WHERE p.supp = 0
              AND (REGEXP_REPLACE(p.numero_telephone_tuteur, '[^0-9]', '') = :phone
                OR REGEXP_REPLACE(p.telephonepere, '[^0-9]', '') = :phone
                OR REGEXP_REPLACE(p.telephonemere, '[^0-9]', '') = :phone)
            ORDER BY p.id ASC
        SQL;

        return array_map('intval', $connection->fetchFirstColumn($sql, ['phone' => $normalizedTelephone]));
    }

    public function findActiveByTelephoneTuteur(string $normalizedTelephone): ?ParentEleve
    {
        $ids = $this->findActiveIdsByTelephoneTuteur($normalizedTelephone);

        if ($ids === []) {
            return null;
        }

        return $this->find($ids[0]);
    }

    public function telephoneMatchesParent(ParentEleve $parent, string $normalizedTelephone): bool
    {
        return PhoneNormalizer::normalize($parent->getNumeroTelephoneTuteur()) === $normalizedTelephone;
    }
}
