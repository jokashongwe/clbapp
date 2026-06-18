<?php

namespace App\Controller;

use App\Repository\AnneeScolaireRepository;
use App\Repository\EleveRepository;
use App\Repository\PaiementRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/paiements')]
final class PaiementController extends AbstractApiController
{
    #[Route('', name: 'api_paiements_list', methods: ['GET'])]
    public function list(Request $request, PaiementRepository $repository): JsonResponse
    {
        $eleveId = $request->query->get('eleve_id');
        $tarificationId = $request->query->get('tarification_id');
        $mois = $request->query->get('mois');

        $paiements = $repository->findByFilters(
            eleveId: $eleveId !== null ? (int) $eleveId : null,
            tarificationId: $tarificationId !== null ? (int) $tarificationId : null,
            mois: $mois,
        );

        return $this->jsonRead($paiements);
    }

    #[Route('/eleve/{eleveId}/annee-scolaire/{anneescolaireId}', name: 'api_paiements_eleve_annee_scolaire', methods: ['GET'], requirements: ['eleveId' => '\d+', 'anneescolaireId' => '\d+'])]
    public function byEleveAndAnneeScolaire(
        int $eleveId,
        int $anneescolaireId,
        EleveRepository $eleveRepository,
        PaiementRepository $paiementRepository,
    ): JsonResponse {
        $this->assertEleveExists($eleveId, $eleveRepository);

        $paiements = $paiementRepository->findByEleveAndAnneeScolaire($eleveId, $anneescolaireId);

        return $this->jsonRead($paiements);
    }

    #[Route('/eleve/{eleveId}/annee-scolaire-courante', name: 'api_paiements_eleve_annee_scolaire_courante', methods: ['GET'], requirements: ['eleveId' => '\d+'])]
    public function byEleveAndAnneeScolaireCourante(
        int $eleveId,
        EleveRepository $eleveRepository,
        PaiementRepository $paiementRepository,
        AnneeScolaireRepository $anneeScolaireRepository,
    ): JsonResponse {
        $this->assertEleveExists($eleveId, $eleveRepository);

        $anneeScolaire = $anneeScolaireRepository->findEnCours();
        if ($anneeScolaire === null) {
            throw new NotFoundHttpException('Aucune année scolaire en cours trouvée.');
        }

        $paiements = $paiementRepository->findByEleveAndAnneeScolaire($eleveId, $anneeScolaire->getId());

        return $this->jsonRead([
            'annee_scolaire' => $anneeScolaire,
            'paiements' => $paiements,
        ]);
    }

    #[Route('/eleve/{eleveId}/mois/{mois}', name: 'api_paiements_eleve_mois', methods: ['GET'], requirements: ['eleveId' => '\d+', 'mois' => '.+'])]
    public function byEleveAndMois(
        int $eleveId,
        string $mois,
        EleveRepository $eleveRepository,
        PaiementRepository $paiementRepository,
    ): JsonResponse {
        $this->assertEleveExists($eleveId, $eleveRepository);

        $paiements = $paiementRepository->findByEleveAndMois($eleveId, $mois);

        return $this->jsonRead($paiements);
    }

    #[Route('/{id}', name: 'api_paiements_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, PaiementRepository $repository): JsonResponse
    {
        $paiement = $repository->find($id);

        if ($paiement === null) {
            throw new NotFoundHttpException('Paiement introuvable.');
        }

        return $this->jsonRead($paiement);
    }

    private function assertEleveExists(int $eleveId, EleveRepository $eleveRepository): void
    {
        $eleve = $eleveRepository->find($eleveId);

        if ($eleve === null || $eleve->isSupp()) {
            throw new NotFoundHttpException('Élève introuvable.');
        }
    }
}
