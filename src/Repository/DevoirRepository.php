<?php

namespace App\Repository;

use App\Entity\Devoir;
use App\Repository\Trait\InscriptionActifSqlTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Devoir>
 */
class DevoirRepository extends ServiceEntityRepository
{
    use InscriptionActifSqlTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly AnneeScolaireRepository $anneeScolaireRepository,
    ) {
        parent::__construct($registry, Devoir::class);
    }

    protected function getAnneeScolaireRepository(): AnneeScolaireRepository
    {
        return $this->anneeScolaireRepository;
    }

    /**
     * @return Devoir[]
     */
    public function findByClasseId(int $classeId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.supp = false')
            ->andWhere('d.classeId = :classeId')
            ->setParameter('classeId', $classeId)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Devoir[]
     */
    public function findVisibleForParent(int $parentId, ?int $classeId = null): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT d.id
            FROM devoir d
            WHERE d.supp = 0
              AND EXISTS (
                SELECT 1
                FROM eleve e
                WHERE e.id_classe_id = d.classe_id
                  AND e.id_parent_id = :parentId
                  AND e.supp = 0
        SQL;

        $sql .= $this->buildInscriptionActifSqlFragment('e.id');
        $sql .= <<<'SQL'

              )
        SQL;

        $params = ['parentId' => $parentId];
        $types = [];

        if ($classeId !== null) {
            $sql .= ' AND d.classe_id = :classeId';
            $params['classeId'] = $classeId;
        }

        $sql .= <<<'SQL'

            ORDER BY d.created_at DESC
        SQL;

        $this->appendInscriptionActifQueryParams($params, $types);

        $ids = $connection->fetchFirstColumn($sql, $params, $types);

        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('d')
            ->andWhere('d.id IN (:ids)')
            ->setParameter('ids', array_map('intval', $ids))
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int[]
     */
    public function findRecipientParentIdsForClasse(int $classeId): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT DISTINCT e.id_parent_id
            FROM eleve e
            WHERE e.id_classe_id = :classeId
              AND e.supp = 0
              AND e.id_parent_id IS NOT NULL
        SQL;

        $sql .= $this->buildInscriptionActifSqlFragment('e.id');

        $params = ['classeId' => $classeId];
        $types = [];
        $this->appendInscriptionActifQueryParams($params, $types);

        $ids = $connection->fetchFirstColumn($sql, $params, $types);

        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function canParentAccessDevoir(Devoir $devoir, int $parentId): bool
    {
        if ($devoir->isSupp()) {
            return false;
        }

        return $this->parentHasAccessToClasse($parentId, $devoir->getClasseId());
    }

    public function parentHasAccessToClasse(int $parentId, int $classeId): bool
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT 1
            FROM eleve e
            WHERE e.id_classe_id = :classeId
              AND e.id_parent_id = :parentId
              AND e.supp = 0
        SQL;

        $sql .= $this->buildInscriptionActifSqlFragment('e.id');

        $params = [
            'parentId' => $parentId,
            'classeId' => $classeId,
        ];
        $types = [];
        $this->appendInscriptionActifQueryParams($params, $types);

        return $connection->fetchOne($sql, $params, $types) !== false;
    }
}
