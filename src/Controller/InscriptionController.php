<?php

namespace App\Controller;

use App\Entity\AnneeScolaire;
use App\Repository\AnneeScolaireRepository;
use App\Repository\InscriptionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/inscriptions')]
final class InscriptionController extends AbstractApiController
{
    #[Route('/statistiques/annee-scolaire-courante', name: 'api_inscriptions_statistiques_annee_courante', methods: ['GET'])]
    public function statistiquesAnneeScolaireCourante(
        AnneeScolaireRepository $anneeScolaireRepository,
        InscriptionRepository $inscriptionRepository,
    ): JsonResponse {
        $anneeScolaire = $anneeScolaireRepository->findEnCours();
        if ($anneeScolaire === null) {
            throw new NotFoundHttpException('Aucune année scolaire en cours trouvée.');
        }

        return $this->statistiquesResponse($anneeScolaire, $inscriptionRepository);
    }

    #[Route('/statistiques/annee-scolaire/{anneescolaireId}', name: 'api_inscriptions_statistiques_annee_scolaire', methods: ['GET'], requirements: ['anneescolaireId' => '\d+'])]
    public function statistiquesParAnneeScolaire(
        int $anneescolaireId,
        AnneeScolaireRepository $anneeScolaireRepository,
        InscriptionRepository $inscriptionRepository,
    ): JsonResponse {
        $anneeScolaire = $anneeScolaireRepository->find($anneescolaireId);
        if ($anneeScolaire === null || $anneeScolaire->isSupp()) {
            throw new NotFoundHttpException('Année scolaire introuvable.');
        }

        return $this->statistiquesResponse($anneeScolaire, $inscriptionRepository);
    }

    private function statistiquesResponse(
        AnneeScolaire $anneeScolaire,
        InscriptionRepository $inscriptionRepository,
    ): JsonResponse {
        $statistiques = $inscriptionRepository->getStatistiquesParCategorieEtType(
            $anneeScolaire->getAnneeScolaire(),
        );

        return $this->json([
            'annee_scolaire' => [
                'id' => $anneeScolaire->getId(),
                'anneeScolaire' => $anneeScolaire->getAnneeScolaire(),
                'status' => $anneeScolaire->getStatus(),
                'dateDebut' => $anneeScolaire->getDateDebut()->format('Y-m-d'),
                'dateFin' => $anneeScolaire->getDateFin()->format('Y-m-d'),
            ],
            'par_categorie' => $statistiques['par_categorie'],
            'totaux' => $statistiques['totaux'],
        ]);
    }
}
