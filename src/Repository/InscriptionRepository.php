<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

final class InscriptionRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{
     *     par_categorie: list<array{
     *         categorie: array{id: int, section: ?string, option: ?string},
     *         nouveau: int,
     *         ancien: int,
     *         total: int
     *     }>,
     *     totaux: array{nouveau: int, ancien: int, total: int}
     * }
     */
    public function getStatistiquesParCategorieEtType(string $anneeScolaire): array
    {
        $connection = $this->entityManager->getConnection();

        $sql = <<<'SQL'
            SELECT
                c.id AS categorie_id,
                c.section AS categorie_section,
                c.`option` AS categorie_option,
                i.type AS inscription_type,
                COUNT(i.id) AS total
            FROM inscription i
            INNER JOIN type_classe tc ON i.id_type_classe_id = tc.id
            INNER JOIN categorie c ON tc.id_categorie_id = c.id
            WHERE i.supp = 0
              AND i.anneescolaire = :anneeScolaire
              AND i.type IN ('Nouveau', 'Ancien')
            GROUP BY c.id, c.section, c.`option`, i.type
            ORDER BY c.section ASC, c.`option` ASC
        SQL;

        $rows = $connection->fetchAllAssociative($sql, ['anneeScolaire' => $anneeScolaire]);

        $categories = [];
        $totaux = ['nouveau' => 0, 'ancien' => 0, 'total' => 0];

        foreach ($rows as $row) {
            $categorieId = (int) $row['categorie_id'];

            if (!isset($categories[$categorieId])) {
                $categories[$categorieId] = [
                    'categorie' => [
                        'id' => $categorieId,
                        'section' => $row['categorie_section'],
                        'option' => $row['categorie_option'],
                    ],
                    'nouveau' => 0,
                    'ancien' => 0,
                    'total' => 0,
                ];
            }

            $count = (int) $row['total'];
            $type = strtolower((string) $row['inscription_type']);

            $categories[$categorieId][$type] = $count;
            $categories[$categorieId]['total'] += $count;
            $totaux[$type] += $count;
            $totaux['total'] += $count;
        }

        return [
            'par_categorie' => array_values($categories),
            'totaux' => $totaux,
        ];
    }
}
