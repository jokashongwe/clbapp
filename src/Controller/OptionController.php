<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Options / filières de l'école — table `categorie` de la BD principale.
 * Utilisé notamment comme cible `option` des annonces (`cibleId` = categorie.id).
 */
#[Route('/api/options')]
final class OptionController extends AbstractApiController
{
    #[Route('', name: 'api_options_list', methods: ['GET'])]
    public function list(CategorieRepository $categorieRepository): JsonResponse
    {
        return $this->json($categorieRepository->findAllOptions());
    }

    #[Route('/{id}', name: 'api_options_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, CategorieRepository $categorieRepository): JsonResponse
    {
        $option = $categorieRepository->findOptionById($id);
        if ($option === null) {
            throw new NotFoundHttpException('Option (catégorie) introuvable.');
        }

        return $this->json($option);
    }
}
