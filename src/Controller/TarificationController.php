<?php

namespace App\Controller;

use App\Repository\TarificationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tarifications')]
final class TarificationController extends AbstractApiController
{
    #[Route('', name: 'api_tarifications_list', methods: ['GET'])]
    public function list(Request $request, TarificationRepository $repository): JsonResponse
    {
        $nom = $request->query->get('nom');

        $tarifications = $nom !== null && $nom !== ''
            ? $repository->findByNom($nom)
            : $repository->findBy([], ['nom' => 'ASC']);

        return $this->jsonRead($tarifications);
    }

    #[Route('/{id}', name: 'api_tarifications_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, TarificationRepository $repository): JsonResponse
    {
        $tarification = $repository->find($id);

        if ($tarification === null) {
            throw new NotFoundHttpException('Tarification introuvable.');
        }

        return $this->jsonRead($tarification);
    }
}
