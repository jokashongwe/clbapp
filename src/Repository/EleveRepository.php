<?php

namespace App\Repository;

use App\Entity\Eleve;
use App\Repository\AnneeScolaireRepository;
use App\Repository\Trait\InscriptionActifSqlTrait;
use App\Util\PhoneNormalizer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Eleve>
 */
class EleveRepository extends ServiceEntityRepository
{
    use InscriptionActifSqlTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly AnneeScolaireRepository $anneeScolaireRepository,
    ) {
        parent::__construct($registry, Eleve::class);
    }

    protected function getAnneeScolaireRepository(): AnneeScolaireRepository
    {
        return $this->anneeScolaireRepository;
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
        SQL;

        $sql .= $this->buildInscriptionActifSqlFragment('e.id');
        $sql .= <<<'SQL'

            ORDER BY e.nom ASC, e.post_nom ASC, e.prenom ASC
        SQL;

        $params = ['phones' => $variants];
        $types = ['phones' => ArrayParameterType::STRING];
        $this->appendInscriptionActifQueryParams($params, $types);

        $ids = $connection->fetchFirstColumn(
            $sql,
            $params,
            $types,
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
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT e.id
            FROM eleve e
            INNER JOIN parent_eleve p ON e.id_parent_id = p.id
            WHERE p.id = :parentId
              AND e.supp = 0
              AND p.supp = 0
        SQL;

        $sql .= $this->buildInscriptionActifSqlFragment('e.id');
        $sql .= <<<'SQL'

            ORDER BY e.nom ASC, e.post_nom ASC, e.prenom ASC
        SQL;

        $params = ['parentId' => $parentId];
        $types = [];
        $this->appendInscriptionActifQueryParams($params, $types);

        $ids = $connection->fetchFirstColumn($sql, $params, $types);

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
}
