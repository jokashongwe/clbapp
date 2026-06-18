<?php

namespace App\Controller;

use App\Repository\AbsenceEleveRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/absences-eleves')]
final class AbsenceEleveController extends AbstractApiController
{
    #[Route('', name: 'api_absences_eleves_list', methods: ['GET'])]
    public function list(Request $request, AbsenceEleveRepository $repository): JsonResponse
    {
        $dateDebut = $request->query->get('date_debut');
        $dateFin = $request->query->get('date_fin');

        $absences = $repository->findByFilters(
            eleve: $request->query->get('eleve'),
            classe: $request->query->get('classe'),
            section: $request->query->get('section'),
            anneescolaire: $request->query->get('anneescolaire'),
            statut: $request->query->get('statut'),
            dateDebut: $dateDebut ? new \DateTimeImmutable($dateDebut) : null,
            dateFin: $dateFin ? new \DateTimeImmutable($dateFin) : null,
        );

        return $this->jsonRead($absences);
    }
}
