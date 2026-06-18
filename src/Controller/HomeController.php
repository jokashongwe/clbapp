<?php

namespace App\Controller;

use App\Repository\AbonnementRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class HomeController extends AbstractApiController
{
    #[Route('', name: 'api_home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(["message" => "API OK"]);
    }

    
}
