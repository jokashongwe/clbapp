<?php

namespace App\Controller;

use App\Entity\AnneeScolaire;
use App\Repository\AnneeScolaireRepository;
use App\Repository\PaiementStatistiqueRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/paiements/statistiques')]
final class PaiementStatistiqueController extends AbstractApiController
{
    #[Route('/par-tarification/annee-scolaire-courante', name: 'api_paiements_stats_tarification_annee_courante', methods: ['GET'])]
    public function parTarificationAnneeCourante(
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parTarification(
            $this->resolveAnneeCourante($anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-tarification/annee-scolaire/{anneescolaireId}', name: 'api_paiements_stats_tarification_annee_scolaire', methods: ['GET'], requirements: ['anneescolaireId' => '\d+'])]
    public function parTarificationAnneeScolaire(
        int $anneescolaireId,
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parTarification(
            $this->resolveAnneeScolaire($anneescolaireId, $anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-categorie/annee-scolaire-courante', name: 'api_paiements_stats_categorie_annee_courante', methods: ['GET'])]
    public function parCategorieAnneeCourante(
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parCategorie(
            $this->resolveAnneeCourante($anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-categorie/annee-scolaire/{anneescolaireId}', name: 'api_paiements_stats_categorie_annee_scolaire', methods: ['GET'], requirements: ['anneescolaireId' => '\d+'])]
    public function parCategorieAnneeScolaire(
        int $anneescolaireId,
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parCategorie(
            $this->resolveAnneeScolaire($anneescolaireId, $anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-mois/annee-scolaire-courante', name: 'api_paiements_stats_mois_annee_courante', methods: ['GET'])]
    public function parMoisAnneeCourante(
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parMois(
            $this->resolveAnneeCourante($anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-mois/annee-scolaire/{anneescolaireId}', name: 'api_paiements_stats_mois_annee_scolaire', methods: ['GET'], requirements: ['anneescolaireId' => '\d+'])]
    public function parMoisAnneeScolaire(
        int $anneescolaireId,
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parMois(
            $this->resolveAnneeScolaire($anneescolaireId, $anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-type-paiement/annee-scolaire-courante', name: 'api_paiements_stats_type_annee_courante', methods: ['GET'])]
    public function parTypePaiementAnneeCourante(
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parTypePaiement(
            $this->resolveAnneeCourante($anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-type-paiement/annee-scolaire/{anneescolaireId}', name: 'api_paiements_stats_type_annee_scolaire', methods: ['GET'], requirements: ['anneescolaireId' => '\d+'])]
    public function parTypePaiementAnneeScolaire(
        int $anneescolaireId,
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parTypePaiement(
            $this->resolveAnneeScolaire($anneescolaireId, $anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-caisse/annee-scolaire-courante', name: 'api_paiements_stats_caisse_annee_courante', methods: ['GET'])]
    public function parCaisseAnneeCourante(
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parCaisse(
            $this->resolveAnneeCourante($anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/par-caisse/annee-scolaire/{anneescolaireId}', name: 'api_paiements_stats_caisse_annee_scolaire', methods: ['GET'], requirements: ['anneescolaireId' => '\d+'])]
    public function parCaisseAnneeScolaire(
        int $anneescolaireId,
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->parCaisse(
            $this->resolveAnneeScolaire($anneescolaireId, $anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/resume/annee-scolaire-courante', name: 'api_paiements_stats_resume_annee_courante', methods: ['GET'])]
    public function resumeAnneeCourante(
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->resume(
            $this->resolveAnneeCourante($anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    #[Route('/resume/annee-scolaire/{anneescolaireId}', name: 'api_paiements_stats_resume_annee_scolaire', methods: ['GET'], requirements: ['anneescolaireId' => '\d+'])]
    public function resumeAnneeScolaire(
        int $anneescolaireId,
        AnneeScolaireRepository $anneeScolaireRepository,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->resume(
            $this->resolveAnneeScolaire($anneescolaireId, $anneeScolaireRepository),
            $statistiqueRepository,
        );
    }

    private function parTarification(
        AnneeScolaire $anneeScolaire,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->statistiquesResponse(
            $anneeScolaire,
            'par_tarification',
            $statistiqueRepository->getParTarification($anneeScolaire->getId()),
        );
    }

    private function parCategorie(
        AnneeScolaire $anneeScolaire,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->statistiquesResponse(
            $anneeScolaire,
            'par_categorie',
            $statistiqueRepository->getParCategorie($anneeScolaire->getId()),
        );
    }

    private function parMois(
        AnneeScolaire $anneeScolaire,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->statistiquesResponse(
            $anneeScolaire,
            'par_mois',
            $statistiqueRepository->getParMois($anneeScolaire->getId()),
        );
    }

    private function parTypePaiement(
        AnneeScolaire $anneeScolaire,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->statistiquesResponse(
            $anneeScolaire,
            'par_type_paiement',
            $statistiqueRepository->getParTypePaiement($anneeScolaire->getId()),
        );
    }

    private function parCaisse(
        AnneeScolaire $anneeScolaire,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->statistiquesResponse(
            $anneeScolaire,
            'par_caisse',
            $statistiqueRepository->getParCaisse($anneeScolaire->getId()),
        );
    }

    private function resume(
        AnneeScolaire $anneeScolaire,
        PaiementStatistiqueRepository $statistiqueRepository,
    ): JsonResponse {
        return $this->statistiquesResponse(
            $anneeScolaire,
            'resume',
            $statistiqueRepository->getResume($anneeScolaire->getId()),
        );
    }

    private function resolveAnneeCourante(AnneeScolaireRepository $repository): AnneeScolaire
    {
        $anneeScolaire = $repository->findEnCours();
        if ($anneeScolaire === null) {
            throw new NotFoundHttpException('Aucune année scolaire en cours trouvée.');
        }

        return $anneeScolaire;
    }

    private function resolveAnneeScolaire(int $anneescolaireId, AnneeScolaireRepository $repository): AnneeScolaire
    {
        $anneeScolaire = $repository->find($anneescolaireId);
        if ($anneeScolaire === null || $anneeScolaire->isSupp()) {
            throw new NotFoundHttpException('Année scolaire introuvable.');
        }

        return $anneeScolaire;
    }

    private function statistiquesResponse(AnneeScolaire $anneeScolaire, string $key, mixed $data): JsonResponse
    {
        return $this->json([
            'annee_scolaire' => [
                'id' => $anneeScolaire->getId(),
                'anneeScolaire' => $anneeScolaire->getAnneeScolaire(),
                'status' => $anneeScolaire->getStatus(),
                'dateDebut' => $anneeScolaire->getDateDebut()->format('Y-m-d'),
                'dateFin' => $anneeScolaire->getDateFin()->format('Y-m-d'),
            ],
            $key => $data,
        ]);
    }
}
