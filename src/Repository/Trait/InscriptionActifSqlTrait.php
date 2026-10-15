<?php

namespace App\Repository\Trait;

use App\Repository\AnneeScolaireRepository;

trait InscriptionActifSqlTrait
{
    abstract protected function getAnneeScolaireRepository(): AnneeScolaireRepository;

    private function buildInscriptionActifSqlFragment(string $eleveIdColumn): string
    {
        $anneeScolaire = $this->getAnneeScolaireCouranteLabel();

        if ($anneeScolaire !== null) {
            return <<<SQL

              AND EXISTS (
                SELECT 1
                FROM inscription i
                WHERE i.id_eleve_id = {$eleveIdColumn}
                  AND i.supp = 0
                  AND i.anneescolaire = :anneeScolaire
              )
              AND NOT EXISTS (
                SELECT 1
                FROM inscription i
                WHERE i.id_eleve_id = {$eleveIdColumn}
                  AND i.supp = 0
                  AND i.anneescolaire = :anneeScolaire
                  AND i.type = 'Quitter'
              )
            SQL;
        }

        return "

              AND NOT EXISTS (
                SELECT 1
                FROM inscription i
                WHERE i.id_eleve_id = {$eleveIdColumn}
                  AND i.supp = 0
                  AND i.type = 'Quitter'
              )
        ";
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $types
     */
    private function appendInscriptionActifQueryParams(array &$params, array &$types): void
    {
        $anneeScolaire = $this->getAnneeScolaireCouranteLabel();

        if ($anneeScolaire !== null) {
            $params['anneeScolaire'] = $anneeScolaire;
        }
    }

    private function getAnneeScolaireCouranteLabel(): ?string
    {
        return $this->getAnneeScolaireRepository()->findEnCours()?->getAnneeScolaire();
    }
}
