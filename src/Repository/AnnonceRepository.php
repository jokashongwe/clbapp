<?php

namespace App\Repository;

use App\Entity\Annonce;
use App\Repository\Trait\InscriptionActifSqlTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Annonce>
 */
class AnnonceRepository extends ServiceEntityRepository
{
    use InscriptionActifSqlTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly AnneeScolaireRepository $anneeScolaireRepository,
    ) {
        parent::__construct($registry, Annonce::class);
    }

    protected function getAnneeScolaireRepository(): AnneeScolaireRepository
    {
        return $this->anneeScolaireRepository;
    }

    /**
     * @return Annonce[]
     */
    public function findUnreadForParent(int $parentId): array
    {
        $ids = $this->findUnreadIdsForParent($parentId);

        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('a')
            ->andWhere('a.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function isVisibleForParent(Annonce $annonce, int $parentId): bool
    {
        if ($annonce->isSupp()) {
            return false;
        }

        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT 1
            FROM annonce a
            WHERE a.id = :annonceId
              AND a.supp = 0
        SQL;

        $sql .= ' AND '.$this->buildCibleMatchSql('a');

        $params = [
            'annonceId' => $annonce->getId(),
            'parentId' => $parentId,
        ];
        $types = [];
        $this->appendInscriptionActifQueryParams($params, $types);

        $result = $connection->fetchOne($sql, $params, $types);

        return $result !== false;
    }

    /**
     * @return int[]
     */
    public function findUnreadIdsForParent(int $parentId): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT a.id
            FROM annonce a
            WHERE a.supp = 0
              AND NOT EXISTS (
                SELECT 1
                FROM annonce_lecture al
                WHERE al.annonce_id = a.id
                  AND al.parent_id = :parentId
              )
        SQL;

        $sql .= ' AND '.$this->buildCibleMatchSql('a');
        $sql .= <<<'SQL'

            ORDER BY a.created_at DESC
        SQL;

        $params = ['parentId' => $parentId];
        $types = [];
        $this->appendInscriptionActifQueryParams($params, $types);

        $ids = $connection->fetchFirstColumn($sql, $params, $types);

        return array_map('intval', $ids);
    }

    /**
     * @return int[]
     */
    public function findRecipientParentIds(Annonce $annonce): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $params = [];
        $types = [];

        $cibleType = $annonce->getCibleType();

        if ($cibleType === Annonce::CIBLE_TOUS) {
            $sql = <<<'SQL'
                SELECT DISTINCT p.id
                FROM parent_eleve p
                WHERE p.supp = 0
            SQL;
        } elseif ($cibleType === Annonce::CIBLE_ELEVE) {
            $sql = <<<'SQL'
                SELECT DISTINCT e.id_parent_id
                FROM eleve e
                WHERE e.id = :cibleId
                  AND e.supp = 0
                  AND e.id_parent_id IS NOT NULL
            SQL;
            $sql .= $this->buildInscriptionActifSqlFragment('e.id');
            $params['cibleId'] = $annonce->getCibleId();
        } elseif ($cibleType === Annonce::CIBLE_CLASSE) {
            $sql = <<<'SQL'
                SELECT DISTINCT e.id_parent_id
                FROM eleve e
                WHERE e.id_classe_id = :cibleId
                  AND e.supp = 0
                  AND e.id_parent_id IS NOT NULL
            SQL;
            $sql .= $this->buildInscriptionActifSqlFragment('e.id');
            $params['cibleId'] = $annonce->getCibleId();
        } elseif ($cibleType === Annonce::CIBLE_OPTION) {
            $sql = <<<'SQL'
                SELECT DISTINCT e.id_parent_id
                FROM eleve e
                INNER JOIN classe c ON c.id = e.id_classe_id
                WHERE c.id_categorie_id = :cibleId
                  AND e.supp = 0
                  AND e.id_parent_id IS NOT NULL
            SQL;
            $sql .= $this->buildInscriptionActifSqlFragment('e.id');
            $params['cibleId'] = $annonce->getCibleId();
        } else {
            return [];
        }

        $this->appendInscriptionActifQueryParams($params, $types);

        $ids = $connection->fetchFirstColumn($sql, $params, $types);

        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function markAllUnreadAsReadForParent(int $parentId): int
    {
        $ids = $this->findUnreadIdsForParent($parentId);

        if ($ids === []) {
            return 0;
        }

        $connection = $this->getEntityManager()->getConnection();
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $count = 0;
        foreach ($ids as $annonceId) {
            $inserted = $connection->executeStatement(
                'INSERT IGNORE INTO annonce_lecture (annonce_id, parent_id, read_at) VALUES (:annonceId, :parentId, :readAt)',
                [
                    'annonceId' => $annonceId,
                    'parentId' => $parentId,
                    'readAt' => $now,
                ],
            );
            $count += $inserted;
        }

        return $count;
    }

    private function buildCibleMatchSql(string $alias): string
    {
        $inscriptionEleve = $this->buildInscriptionActifSqlFragment('e.id');
        $inscriptionEleveClasse = $this->buildInscriptionActifSqlFragment('e2.id');
        $inscriptionEleveOption = $this->buildInscriptionActifSqlFragment('e3.id');

        return <<<SQL
(
  {$alias}.cible_type = 'tous'
  OR (
    {$alias}.cible_type = 'eleve'
    AND EXISTS (
      SELECT 1
      FROM eleve e
      WHERE e.id = {$alias}.cible_id
        AND e.id_parent_id = :parentId
        AND e.supp = 0
      {$inscriptionEleve}
    )
  )
  OR (
    {$alias}.cible_type = 'classe'
    AND EXISTS (
      SELECT 1
      FROM eleve e2
      WHERE e2.id_classe_id = {$alias}.cible_id
        AND e2.id_parent_id = :parentId
        AND e2.supp = 0
      {$inscriptionEleveClasse}
    )
  )
  OR (
    {$alias}.cible_type = 'option'
    AND EXISTS (
      SELECT 1
      FROM eleve e3
      INNER JOIN classe c ON c.id = e3.id_classe_id
      WHERE c.id_categorie_id = {$alias}.cible_id
        AND e3.id_parent_id = :parentId
        AND e3.supp = 0
      {$inscriptionEleveOption}
    )
  )
)
SQL;
    }
}
