<?php

namespace App\Controller;

use App\Repository\AbonnementRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/abonnements')]
final class AbonnementController extends AbstractApiController
{
    #[Route('', name: 'api_abonnements_list', methods: ['GET'])]
    public function list(Request $request, AbonnementRepository $repository): JsonResponse
    {
        $actif = $request->query->getBoolean('actif');

        $abonnements = $actif
            ? $repository->findActifs()
            : $repository->findBy([], ['datedebut' => 'DESC']);

        return $this->jsonRead($abonnements);
    }

    #[Route('/{id}', name: 'api_abonnements_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, AbonnementRepository $repository): JsonResponse
    {
        $abonnement = $repository->find($id);

        if ($abonnement === null) {
            throw new NotFoundHttpException('Abonnement introuvable.');
        }

        return $this->jsonRead($abonnement);
    }
}
