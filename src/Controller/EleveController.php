<?php

namespace App\Controller;

use App\Repository\AbsenceEleveRepository;
use App\Repository\EleveRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/eleves')]
final class EleveController extends AbstractApiController
{
    #[Route('', name: 'api_eleves_list', methods: ['GET'])]
    public function list(Request $request, EleveRepository $repository): JsonResponse
    {
        $telephone = $request->query->get('numero_telephone_tuteur');

        if ($telephone === null || $telephone === '') {
            throw new BadRequestHttpException('Le paramètre numero_telephone_tuteur est requis.');
        }

        $eleves = $repository->findByNumeroTelephoneTuteur($telephone);

        return $this->jsonRead($eleves);
    }

    #[Route('/by-parent/{parentId}', name: 'api_eleves_by_parent', methods: ['GET'], requirements: ['parentId' => '\d+'])]
    public function byParent(int $parentId, EleveRepository $repository): JsonResponse
    {
        $eleves = $repository->findByParentId($parentId);

        return $this->jsonRead($eleves);
    }

    #[Route('/{eleveId}/absences', name: 'api_eleves_absences', methods: ['GET'], requirements: ['eleveId' => '\d+'])]
    public function absences(
        int $eleveId,
        EleveRepository $eleveRepository,
        AbsenceEleveRepository $absenceEleveRepository,
    ): JsonResponse {
        $eleve = $eleveRepository->find($eleveId);

        if ($eleve === null || $eleve->isSupp()) {
            throw new NotFoundHttpException('Élève introuvable.');
        }

        $absences = $absenceEleveRepository->findByEleve($eleve);

        return $this->jsonRead($absences);
    }

    #[Route('/{id}', name: 'api_eleves_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, EleveRepository $repository): JsonResponse
    {
        $eleve = $repository->find($id);

        if ($eleve === null || $eleve->isSupp()) {
            throw new NotFoundHttpException('Élève introuvable.');
        }

        return $this->jsonRead($eleve);
    }
}
