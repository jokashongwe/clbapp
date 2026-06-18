<?php

namespace App\Controller;

use App\Service\ParentAuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
final class AuthController extends AbstractApiController
{
    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request, ParentAuthService $parentAuthService): JsonResponse
    {
        $data = $this->decodeJsonBody($request);

        $telephone = $data['telephone'] ?? null;
        $pin = $data['pin'] ?? null;

        if (!is_string($telephone) || $telephone === '' || !is_string($pin) || $pin === '') {
            throw new BadRequestHttpException('Les champs telephone et pin sont requis.');
        }

        return $this->json($parentAuthService->login($telephone, $pin));
    }

    #[Route('/change-pin', name: 'api_auth_change_pin', methods: ['POST'])]
    public function changePin(Request $request, ParentAuthService $parentAuthService): JsonResponse
    {
        $data = $this->decodeJsonBody($request);

        $telephone = $data['telephone'] ?? null;
        $currentPin = $data['current_pin'] ?? null;
        $newPin = $data['new_pin'] ?? null;

        if (!is_string($telephone) || $telephone === ''
            || !is_string($currentPin) || $currentPin === ''
            || !is_string($newPin) || $newPin === '') {
            throw new BadRequestHttpException('Les champs telephone, current_pin et new_pin sont requis.');
        }

        $parentAuthService->changePin($telephone, $currentPin, $newPin);

        return $this->json(['message' => 'PIN modifié avec succès.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonBody(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new BadRequestHttpException('Corps JSON invalide.');
        }

        return $data;
    }
}
