<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

final class ClasseRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<array{
     *     id: int,
     *     libelle: string,
     *     nombreEleveMax: int,
     *     nomAffiche: string,
     *     typeClasseId: int,
     *     niveau: array{id: int, libelle: string},
     *     categorie: array{id: int, section: ?string, option: ?string}
     * }>
     */
    public function findAllActive(?int $categorieId = null): array
    {
        $sql = <<<'SQL'
            SELECT
                cl.id,
                cl.libelle,
                cl.nombreelevemax,
                cl.id_type_classe_id,
                n.id AS niveau_id,
                n.libelle AS niveau_libelle,
                c.id AS categorie_id,
                c.section AS categorie_section,
                c.`option` AS categorie_option
            FROM classe cl
            INNER JOIN niveaux n ON cl.id_niveaux_id = n.id
            INNER JOIN categorie c ON cl.id_categorie_id = c.id
            WHERE cl.supp = 0
        SQL;

        $params = [];
        if ($categorieId !== null) {
            $sql .= ' AND cl.id_categorie_id = :categorieId';
            $params['categorieId'] = $categorieId;
        }

        $sql .= <<<'SQL'

            ORDER BY c.section ASC, c.`option` ASC, n.id ASC, cl.libelle ASC, cl.id ASC
        SQL;

        $rows = $this->entityManager->getConnection()->fetchAllAssociative($sql, $params);

        return array_map(
            fn (array $row): array => $this->mapRow($row),
            $rows,
        );
    }

    /**
     * @return array{
     *     id: int,
     *     libelle: string,
     *     nombreEleveMax: int,
     *     nomAffiche: string,
     *     typeClasseId: int,
     *     niveau: array{id: int, libelle: string},
     *     categorie: array{id: int, section: ?string, option: ?string}
     * }|null
     */
    public function findActiveById(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                cl.id,
                cl.libelle,
                cl.nombreelevemax,
                cl.id_type_classe_id,
                n.id AS niveau_id,
                n.libelle AS niveau_libelle,
                c.id AS categorie_id,
                c.section AS categorie_section,
                c.`option` AS categorie_option
            FROM classe cl
            INNER JOIN niveaux n ON cl.id_niveaux_id = n.id
            INNER JOIN categorie c ON cl.id_categorie_id = c.id
            WHERE cl.id = :id
              AND cl.supp = 0
            LIMIT 1
        SQL;

        $row = $this->entityManager->getConnection()->fetchAssociative($sql, ['id' => $id]);
        if ($row === false) {
            return null;
        }

        return $this->mapRow($row);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{
     *     id: int,
     *     libelle: string,
     *     nombreEleveMax: int,
     *     nomAffiche: string,
     *     typeClasseId: int,
     *     niveau: array{id: int, libelle: string},
     *     categorie: array{id: int, section: ?string, option: ?string}
     * }
     */
    private function mapRow(array $row): array
    {
        $niveauLibelle = (string) $row['niveau_libelle'];
        $classeLibelle = (string) $row['libelle'];
        $option = $row['categorie_option'] !== null && $row['categorie_option'] !== '' && $row['categorie_option'] !== 'null'
            ? (string) $row['categorie_option']
            : (string) ($row['categorie_section'] ?? '');

        $nomAffiche = trim(sprintf('%s %s %s', $niveauLibelle, $classeLibelle, $option));

        return [
            'id' => (int) $row['id'],
            'libelle' => $classeLibelle,
            'nombreEleveMax' => (int) $row['nombreelevemax'],
            'nomAffiche' => $nomAffiche,
            'typeClasseId' => (int) $row['id_type_classe_id'],
            'niveau' => [
                'id' => (int) $row['niveau_id'],
                'libelle' => $niveauLibelle,
            ],
            'categorie' => [
                'id' => (int) $row['categorie_id'],
                'section' => $row['categorie_section'],
                'option' => $row['categorie_option'] === 'null' ? null : $row['categorie_option'],
            ],
        ];
    }
}
