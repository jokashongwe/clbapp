<?php

namespace App\Controller;

use App\Entity\ParentPushDevice;
use App\Repository\ParentEleveRepository;
use App\Repository\ParentPushDeviceRepository;
use App\Security\ParentUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/push/devices')]
final class PushDeviceController extends AbstractApiController
{
    #[Route('', name: 'api_push_devices_register', methods: ['POST'])]
    public function register(
        Request $request,
        ParentPushDeviceRepository $pushDeviceRepository,
        ParentEleveRepository $parentEleveRepository,
    ): JsonResponse {
        $parentId = $this->requireParentId();

        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('Corps JSON invalide.');
        }

        $token = isset($payload['token']) ? trim((string) $payload['token']) : '';
        $platform = isset($payload['platform']) ? strtolower(trim((string) $payload['platform'])) : ParentPushDevice::PLATFORM_ANDROID;

        if ($token === '') {
            throw new BadRequestHttpException('Le champ token est requis.');
        }

        if (mb_strlen($token) > 512) {
            throw new BadRequestHttpException('Token trop long.');
        }

        if (!\in_array($platform, ParentPushDevice::PLATFORMS, true)) {
            throw new BadRequestHttpException('platform doit valoir android ou ios.');
        }

        $parent = $parentEleveRepository->find($parentId);
        if ($parent === null || $parent->isSupp()) {
            throw new NotFoundHttpException('Parent introuvable.');
        }

        $device = $pushDeviceRepository->register($parent, $token, $platform);

        return new JsonResponse([
            'ok' => true,
            'id' => $device->getId(),
            'platform' => $device->getPlatform(),
        ], 201);
    }

    #[Route('/unregister', name: 'api_push_devices_unregister', methods: ['POST'])]
    public function unregister(
        Request $request,
        ParentPushDeviceRepository $pushDeviceRepository,
    ): JsonResponse {
        $this->requireParentId();

        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('Corps JSON invalide.');
        }

        $token = isset($payload['token']) ? trim((string) $payload['token']) : '';
        if ($token === '') {
            throw new BadRequestHttpException('Le champ token est requis.');
        }

        $removed = $pushDeviceRepository->unregisterByToken($token);

        return new JsonResponse(['ok' => true, 'removed' => $removed]);
    }

    private function requireParentId(): int
    {
        $user = $this->getUser();
        if (!$user instanceof ParentUser) {
            throw new AccessDeniedHttpException('Réservé à un parent authentifié (session login).');
        }

        return $user->getParentId();
    }
}
