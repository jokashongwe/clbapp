<?php

namespace App\Controller;

use App\Repository\AbsenceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/absences')]
final class AbsenceController extends AbstractApiController
{
    #[Route('', name: 'api_absences_list', methods: ['GET'])]
    public function list(Request $request, AbsenceRepository $repository): JsonResponse
    {
        $agentaffectationId = $request->query->get('agentaffectation_id');

        if ($agentaffectationId !== null) {
            $absences = $repository->findByAgentAffectationId((int) $agentaffectationId);
        } else {
            $absences = $repository->findBy([], ['id' => 'DESC']);
        }

        return $this->jsonRead($absences);
    }

    #[Route('/{id}', name: 'api_absences_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, AbsenceRepository $repository): JsonResponse
    {
        $absence = $repository->find($id);

        if ($absence === null) {
            throw new NotFoundHttpException('Absence introuvable.');
        }

        return $this->jsonRead($absence);
    }
}
