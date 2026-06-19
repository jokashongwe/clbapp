<?php

namespace App\Repository;

use App\Entity\Eleve;
use App\Util\PhoneNormalizer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Eleve>
 */
class EleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eleve::class);
    }

    /**
     * @return Eleve[]
     */
    public function findByNumeroTelephoneTuteur(string $telephone): array
    {
        $variants = PhoneNormalizer::variants($telephone);
        if ($variants === []) {
            return [];
        }

        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT e.id
            FROM eleve e
            INNER JOIN parent_eleve p ON e.id_parent_id = p.id
            WHERE e.supp = 0
              AND p.supp = 0
              AND (
                REGEXP_REPLACE(p.numero_telephone_tuteur, '[^0-9]', '') IN (:phones)
                OR REGEXP_REPLACE(p.telephonepere, '[^0-9]', '') IN (:phones)
                OR REGEXP_REPLACE(p.telephonemere, '[^0-9]', '') IN (:phones)
              )
            ORDER BY e.nom ASC, e.post_nom ASC, e.prenom ASC
        SQL;

        $ids = $connection->fetchFirstColumn(
            $sql,
            ['phones' => $variants],
            ['phones' => ArrayParameterType::STRING],
        );

        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('e')
            ->addSelect('p')
            ->leftJoin('e.parent', 'p')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Eleve[]
     */
    public function findByParentId(int $parentId): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('p')
            ->innerJoin('e.parent', 'p')
            ->andWhere('p.id = :parentId')
            ->andWhere('e.supp = false')
            ->andWhere('p.supp = false')
            ->setParameter('parentId', $parentId)
            ->orderBy('e.nom', 'ASC')
            ->addOrderBy('e.postNom', 'ASC')
            ->addOrderBy('e.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
