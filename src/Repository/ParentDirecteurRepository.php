<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ParentDirecteurRepository
{
    /** @var int[] */
    private array $explicitParentIds;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(env: 'default::DIRECTEUR_PARENT_IDS')]
        string $explicitParentIds = '',
    ) {
        $this->explicitParentIds = $this->parseExplicitParentIds($explicitParentIds);
    }

    public function isDirecteur(int $parentId): bool
    {
        if (\in_array($parentId, $this->explicitParentIds, true)) {
            return true;
        }

        $connection = $this->entityManager->getConnection();

        $sql = <<<'SQL'
            SELECT 1
            FROM parent_eleve p
            INNER JOIN user u ON p.related_user_id = u.id
            WHERE p.id = :parentId
              AND p.supp = 0
              AND u.supp = 0
              AND (
                u.profil = 'admin'
                OR u.roles LIKE '%ROLE_admin%'
                OR EXISTS (
                    SELECT 1
                    FROM agent_affectation aa
                    INNER JOIN grades g ON aa.grades_id = g.id
                    WHERE aa.agent_id = u.agents_id
                      AND aa.supp = 0
                      AND (aa.date_fin IS NULL OR aa.date_fin >= CURDATE())
                      AND g.libelle IN ('Promoteur', 'Administrateur')
                )
              )
            LIMIT 1
        SQL;

        return (bool) $connection->fetchOne($sql, ['parentId' => $parentId]);
    }

    /**
     * @return int[]
     */
    private function parseExplicitParentIds(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_unique(array_map(
            'intval',
            array_filter(array_map('trim', explode(',', $value)), static fn (string $id): bool => $id !== '' && ctype_digit($id)),
        )));
    }
}
