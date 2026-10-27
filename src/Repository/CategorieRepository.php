<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;

final class CategorieRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Options / filières de l'école (`categorie` dans la BD principale).
     *
     * @return list<array{
     *     id: int,
     *     section: ?string,
     *     option: ?string,
     *     nombrePeriodes: ?int,
     *     nomAffiche: string
     * }>
     */
    public function findAllOptions(): array
    {
        $sql = <<<'SQL'
            SELECT
                c.id,
                c.section,
                c.`option`,
                c.nombreperiodes
            FROM categorie c
            ORDER BY c.section ASC, c.`option` ASC, c.id ASC
        SQL;

        $rows = $this->entityManager->getConnection()->fetchAllAssociative($sql);

        return array_map(
            fn (array $row): array => $this->mapRow($row),
            $rows,
        );
    }

    /**
     * @return array{
     *     id: int,
     *     section: ?string,
     *     option: ?string,
     *     nombrePeriodes: ?int,
     *     nomAffiche: string
     * }|null
     */
    public function findOptionById(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                c.id,
                c.section,
                c.`option`,
                c.nombreperiodes
            FROM categorie c
            WHERE c.id = :id
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
     *     section: ?string,
     *     option: ?string,
     *     nombrePeriodes: ?int,
     *     nomAffiche: string
     * }
     */
    private function mapRow(array $row): array
    {
        $option = $row['option'] === 'null' ? null : $row['option'];
        $section = $row['section'];
        $nomAffiche = (string) ($option ?: $section ?: ('Catégorie #'.$row['id']));

        return [
            'id' => (int) $row['id'],
            'section' => $section,
            'option' => $option,
            'nombrePeriodes' => $row['nombreperiodes'] !== null ? (int) $row['nombreperiodes'] : null,
            'nomAffiche' => $nomAffiche,
        ];
    }
}
