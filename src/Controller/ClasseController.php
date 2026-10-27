<?php

namespace App\Controller;

use App\Repository\ClasseRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/classes')]
final class ClasseController extends AbstractApiController
{
    #[Route('', name: 'api_classes_list', methods: ['GET'])]
    public function list(Request $request, ClasseRepository $classeRepository): JsonResponse
    {
        $categorieId = $this->parseOptionalCategorieId($request);

        return $this->json($classeRepository->findAllActive($categorieId));
    }

    #[Route('/{id}', name: 'api_classes_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, ClasseRepository $classeRepository): JsonResponse
    {
        $classe = $classeRepository->findActiveById($id);
        if ($classe === null) {
            throw new NotFoundHttpException('Classe introuvable.');
        }

        return $this->json($classe);
    }

    private function parseOptionalCategorieId(Request $request): ?int
    {
        $raw = $request->query->get('categorieId', $request->query->get('optionId', $request->query->get('categorie_id')));
        if ($raw === null || $raw === '') {
            return null;
        }

        if (!is_numeric($raw)) {
            throw new BadRequestHttpException('categorieId doit être un entier.');
        }

        return (int) $raw;
    }
}
