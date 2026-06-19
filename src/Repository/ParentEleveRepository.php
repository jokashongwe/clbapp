<?php

namespace App\Repository;

use App\Entity\ParentEleve;
use App\Util\PhoneNormalizer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
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
    public function findActiveIdsByTelephoneTuteur(string $telephone): array
    {
        $variants = PhoneNormalizer::variants($telephone);
        if ($variants === []) {
            return [];
        }

        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT p.id
            FROM parent_eleve p
            WHERE p.supp = 0
              AND (
                REGEXP_REPLACE(p.numero_telephone_tuteur, '[^0-9]', '') IN (:phones)
                OR REGEXP_REPLACE(p.telephonepere, '[^0-9]', '') IN (:phones)
                OR REGEXP_REPLACE(p.telephonemere, '[^0-9]', '') IN (:phones)
              )
            ORDER BY p.id ASC
        SQL;

        return array_map(
            'intval',
            $connection->fetchFirstColumn(
                $sql,
                ['phones' => $variants],
                ['phones' => ArrayParameterType::STRING],
            ),
        );
    }

    public function findActiveByTelephoneTuteur(string $telephone): ?ParentEleve
    {
        $ids = $this->findActiveIdsByTelephoneTuteur($telephone);

        if ($ids === []) {
            return null;
        }

        return $this->find($ids[0]);
    }

    public function telephoneMatchesParent(ParentEleve $parent, string $telephone): bool
    {
        foreach ([
            $parent->getNumeroTelephoneTuteur(),
            $parent->getTelephonepere(),
            $parent->getTelephonemere(),
        ] as $storedPhone) {
            if (PhoneNormalizer::matches($storedPhone, $telephone)) {
                return true;
            }
        }

        return false;
    }
}
