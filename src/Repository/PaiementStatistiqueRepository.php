<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

final class PaiementStatistiqueRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<array{
     *     tarification: array{id: int, nom: string, devise: ?string},
     *     nombre_paiements: int,
     *     total_usd: float,
     *     total_fc: float,
     *     total_montant: float
     * }>
     */
    public function getParTarification(int $anneescolaireId): array
    {
        $sql = <<<'SQL'
            SELECT
                t.id AS tarification_id,
                t.nom AS tarification_nom,
                t.devise AS tarification_devise,
                COUNT(p.id) AS nombre_paiements,
                COALESCE(SUM(p.montant_usd), 0) AS total_usd,
                COALESCE(SUM(p.montant_fc), 0) AS total_fc,
                COALESCE(SUM(p.montant), 0) AS total_montant
            FROM paiement p
            INNER JOIN tarification t ON p.tarification_id = t.id
            WHERE p.anneescolaire_id = :anneescolaireId
            GROUP BY t.id, t.nom, t.devise
            ORDER BY t.nom ASC
        SQL;

        return array_map(
            fn (array $row): array => [
                'tarification' => [
                    'id' => (int) $row['tarification_id'],
                    'nom' => $row['tarification_nom'],
                    'devise' => $row['tarification_devise'],
                ],
                ...$this->formatMontants($row),
            ],
            $this->fetchRows($sql, $anneescolaireId),
        );
    }

    /**
     * @return list<array{
     *     categorie: array{id: int, section: ?string, option: ?string},
     *     nombre_paiements: int,
     *     total_usd: float,
     *     total_fc: float,
     *     total_montant: float
     * }>
     */
    public function getParCategorie(int $anneescolaireId): array
    {
        $sql = <<<'SQL'
            SELECT
                c.id AS categorie_id,
                c.section AS categorie_section,
                c.`option` AS categorie_option,
                COUNT(p.id) AS nombre_paiements,
                COALESCE(SUM(p.montant_usd), 0) AS total_usd,
                COALESCE(SUM(p.montant_fc), 0) AS total_fc,
                COALESCE(SUM(p.montant), 0) AS total_montant
            FROM paiement p
            INNER JOIN eleve e ON p.eleve_id = e.id
            INNER JOIN classe cl ON e.id_classe_id = cl.id
            INNER JOIN categorie c ON cl.id_categorie_id = c.id
            WHERE p.anneescolaire_id = :anneescolaireId
              AND e.supp = 0
              AND cl.supp = 0
            GROUP BY c.id, c.section, c.`option`
            ORDER BY c.section ASC, c.`option` ASC
        SQL;

        return array_map(
            fn (array $row): array => [
                'categorie' => [
                    'id' => (int) $row['categorie_id'],
                    'section' => $row['categorie_section'],
                    'option' => $row['categorie_option'],
                ],
                ...$this->formatMontants($row),
            ],
            $this->fetchRows($sql, $anneescolaireId),
        );
    }

    /**
     * @return list<array{
     *     mois: string,
     *     nombre_paiements: int,
     *     total_usd: float,
     *     total_fc: float,
     *     total_montant: float
     * }>
     */
    public function getParMois(int $anneescolaireId): array
    {
        $sql = <<<'SQL'
            SELECT
                p.mois AS mois,
                COUNT(p.id) AS nombre_paiements,
                COALESCE(SUM(p.montant_usd), 0) AS total_usd,
                COALESCE(SUM(p.montant_fc), 0) AS total_fc,
                COALESCE(SUM(p.montant), 0) AS total_montant
            FROM paiement p
            WHERE p.anneescolaire_id = :anneescolaireId
              AND p.mois IS NOT NULL
              AND p.mois != ''
            GROUP BY p.mois
            ORDER BY p.mois ASC
        SQL;

        return array_map(
            fn (array $row): array => [
                'mois' => (string) $row['mois'],
                ...$this->formatMontants($row),
            ],
            $this->fetchRows($sql, $anneescolaireId),
        );
    }

    /**
     * @return list<array{
     *     type_paiement: string,
     *     nombre_paiements: int,
     *     total_usd: float,
     *     total_fc: float,
     *     total_montant: float
     * }>
     */
    public function getParTypePaiement(int $anneescolaireId): array
    {
        $sql = <<<'SQL'
            SELECT
                COALESCE(NULLIF(p.type_paiement, ''), 'Non renseigné') AS type_paiement,
                COUNT(p.id) AS nombre_paiements,
                COALESCE(SUM(p.montant_usd), 0) AS total_usd,
                COALESCE(SUM(p.montant_fc), 0) AS total_fc,
                COALESCE(SUM(p.montant), 0) AS total_montant
            FROM paiement p
            WHERE p.anneescolaire_id = :anneescolaireId
            GROUP BY COALESCE(NULLIF(p.type_paiement, ''), 'Non renseigné')
            ORDER BY nombre_paiements DESC
        SQL;

        return array_map(
            fn (array $row): array => [
                'type_paiement' => (string) $row['type_paiement'],
                ...$this->formatMontants($row),
            ],
            $this->fetchRows($sql, $anneescolaireId),
        );
    }

    /**
     * @return list<array{
     *     caisse: array{id: int, nom: ?string},
     *     nombre_paiements: int,
     *     total_usd: float,
     *     total_fc: float,
     *     total_montant: float
     * }>
     */
    public function getParCaisse(int $anneescolaireId): array
    {
        $sql = <<<'SQL'
            SELECT
                ca.id AS caisse_id,
                ca.nom AS caisse_nom,
                COUNT(p.id) AS nombre_paiements,
                COALESCE(SUM(p.montant_usd), 0) AS total_usd,
                COALESCE(SUM(p.montant_fc), 0) AS total_fc,
                COALESCE(SUM(p.montant), 0) AS total_montant
            FROM paiement p
            INNER JOIN caisse ca ON p.caisse_id = ca.id
            WHERE p.anneescolaire_id = :anneescolaireId
            GROUP BY ca.id, ca.nom
            ORDER BY ca.nom ASC
        SQL;

        return array_map(
            fn (array $row): array => [
                'caisse' => [
                    'id' => (int) $row['caisse_id'],
                    'nom' => $row['caisse_nom'],
                ],
                ...$this->formatMontants($row),
            ],
            $this->fetchRows($sql, $anneescolaireId),
        );
    }

    /**
     * @return array{
     *     nombre_paiements: int,
     *     nombre_eleves: int,
     *     nombre_tarifications: int,
     *     total_usd: float,
     *     total_fc: float,
     *     total_montant: float
     * }
     */
    public function getResume(int $anneescolaireId): array
    {
        $sql = <<<'SQL'
            SELECT
                COUNT(p.id) AS nombre_paiements,
                COUNT(DISTINCT p.eleve_id) AS nombre_eleves,
                COUNT(DISTINCT p.tarification_id) AS nombre_tarifications,
                COALESCE(SUM(p.montant_usd), 0) AS total_usd,
                COALESCE(SUM(p.montant_fc), 0) AS total_fc,
                COALESCE(SUM(p.montant), 0) AS total_montant
            FROM paiement p
            WHERE p.anneescolaire_id = :anneescolaireId
        SQL;

        $row = $this->entityManager->getConnection()->fetchAssociative(
            $sql,
            ['anneescolaireId' => $anneescolaireId],
        );

        if ($row === false) {
            return [
                'nombre_paiements' => 0,
                'nombre_eleves' => 0,
                'nombre_tarifications' => 0,
                'total_usd' => 0.0,
                'total_fc' => 0.0,
                'total_montant' => 0.0,
            ];
        }

        return [
            'nombre_paiements' => (int) $row['nombre_paiements'],
            'nombre_eleves' => (int) $row['nombre_eleves'],
            'nombre_tarifications' => (int) $row['nombre_tarifications'],
            'total_usd' => (float) $row['total_usd'],
            'total_fc' => (float) $row['total_fc'],
            'total_montant' => (float) $row['total_montant'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchRows(string $sql, int $anneescolaireId): array
    {
        return $this->entityManager->getConnection()->fetchAllAssociative(
            $sql,
            ['anneescolaireId' => $anneescolaireId],
        );
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{
     *     nombre_paiements: int,
     *     total_usd: float,
     *     total_fc: float,
     *     total_montant: float
     * }
     */
    private function formatMontants(array $row): array
    {
        return [
            'nombre_paiements' => (int) $row['nombre_paiements'],
            'total_usd' => (float) $row['total_usd'],
            'total_fc' => (float) $row['total_fc'],
            'total_montant' => (float) $row['total_montant'],
        ];
    }
}
