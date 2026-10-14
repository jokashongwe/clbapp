<?php

namespace App\Controller;

use App\Entity\Devoir;
use App\Repository\DevoirRepository;
use App\Repository\ParentEleveRepository;
use App\Security\ParentUser;
use App\Service\CloudflareR2Storage;
use App\Service\DevoirFileValidator;
use App\Service\Push\ParentPushNotificationService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/devoirs')]
final class DevoirController extends AbstractApiController
{
    #[Route('', name: 'api_devoirs_create', methods: ['POST'])]
    #[IsGranted('ROLE_DIRECTEUR')]
    public function create(
        Request $request,
        DevoirRepository $devoirRepository,
        ParentEleveRepository $parentEleveRepository,
        DevoirFileValidator $fileValidator,
        CloudflareR2Storage $storage,
        Connection $connection,
        ParentPushNotificationService $pushNotificationService,
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof ParentUser) {
            throw new AccessDeniedHttpException('Création réservée à un compte parent directeur.');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            throw new BadRequestHttpException('Le champ multipart file est requis (PDF ou Word).');
        }

        $titre = trim((string) $request->request->get('titre', ''));
        $descriptionRaw = $request->request->get('description');
        $description = $descriptionRaw !== null && $descriptionRaw !== ''
            ? trim((string) $descriptionRaw)
            : null;
        $classeIdRaw = $request->request->get('classeId', $request->request->get('classe_id'));

        if ($titre === '') {
            throw new BadRequestHttpException('Le champ titre est requis.');
        }

        if ($classeIdRaw === null || $classeIdRaw === '' || !is_numeric($classeIdRaw)) {
            throw new BadRequestHttpException('Le champ classeId est requis.');
        }

        $classeId = (int) $classeIdRaw;
        $classeExists = $connection->fetchOne('SELECT 1 FROM classe WHERE id = :id', ['id' => $classeId]);
        if ($classeExists === false) {
            throw new BadRequestHttpException('Classe introuvable.');
        }

        $fileValidator->validate($file);
        $mimeType = $fileValidator->resolveMimeType($file);

        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            throw new BadRequestHttpException('Impossible de lire le fichier envoyé.');
        }

        $storageKey = $this->buildStorageKey($file->getClientOriginalName());
        $storage->upload($storageKey, $contents, $mimeType);

        $parent = $parentEleveRepository->find($user->getParentId());
        if ($parent === null || $parent->isSupp()) {
            throw new NotFoundHttpException('Parent introuvable.');
        }

        $devoir = (new Devoir())
            ->setClasseId($classeId)
            ->setTitre($titre)
            ->setDescription($description)
            ->setStorageKey($storageKey)
            ->setOriginalFilename($file->getClientOriginalName())
            ->setMimeType($mimeType)
            ->setFileSize((int) $file->getSize())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreatedBy($parent)
            ->setSupp(false);

        $em = $devoirRepository->getEntityManager();
        $em->persist($devoir);
        $em->flush();

        $pushNotificationService->notifyNewDevoir($devoir);

        return $this->jsonRead($devoir, 201);
    }

    #[Route('', name: 'api_devoirs_list', methods: ['GET'])]
    public function list(Request $request, DevoirRepository $devoirRepository): JsonResponse
    {
        $user = $this->getUser();
        $classeId = $this->parseOptionalClasseId($request);

        if ($user instanceof ParentUser && $user->isDirecteur()) {
            if ($classeId === null) {
                throw new BadRequestHttpException('Le paramètre classeId est requis pour la consultation directeur.');
            }

            return $this->jsonRead($devoirRepository->findByClasseId($classeId));
        }

        $parentId = $this->requireParentId();

        if ($classeId !== null && !$devoirRepository->parentHasAccessToClasse($parentId, $classeId)) {
            throw new NotFoundHttpException('Classe introuvable.');
        }

        return $this->jsonRead($devoirRepository->findVisibleForParent($parentId, $classeId));
    }

    #[Route('/{id}/download-url', name: 'api_devoirs_download_url', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function downloadUrl(
        int $id,
        DevoirRepository $devoirRepository,
        CloudflareR2Storage $storage,
    ): JsonResponse {
        $devoir = $this->loadDevoirWithAccess($id, $devoirRepository);

        $url = $storage->createPresignedDownloadUrl(
            $devoir->getStorageKey(),
            $devoir->getOriginalFilename(),
        );

        return new JsonResponse([
            'downloadUrl' => $url,
            'expiresInSeconds' => $storage->getPresignedUrlTtlSeconds(),
            'filename' => $devoir->getOriginalFilename(),
            'mimeType' => $devoir->getMimeType(),
        ]);
    }

    #[Route('/{id}', name: 'api_devoirs_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, DevoirRepository $devoirRepository): JsonResponse
    {
        $devoir = $this->loadDevoirWithAccess($id, $devoirRepository);

        return $this->jsonRead($devoir);
    }

    private function loadDevoirWithAccess(int $id, DevoirRepository $devoirRepository): Devoir
    {
        $devoir = $devoirRepository->find($id);
        if ($devoir === null || $devoir->isSupp()) {
            throw new NotFoundHttpException('Devoir introuvable.');
        }

        $user = $this->getUser();
        if ($user instanceof ParentUser && $user->isDirecteur()) {
            return $devoir;
        }

        $parentId = $this->requireParentId();
        if (!$devoirRepository->canParentAccessDevoir($devoir, $parentId)) {
            throw new NotFoundHttpException('Devoir introuvable.');
        }

        return $devoir;
    }

    private function requireParentId(): int
    {
        $user = $this->getUser();
        if (!$user instanceof ParentUser) {
            throw new AccessDeniedHttpException('Réservé à un parent authentifié (session login).');
        }

        return $user->getParentId();
    }

    private function parseOptionalClasseId(Request $request): ?int
    {
        $raw = $request->query->get('classeId', $request->query->get('classe_id'));
        if ($raw === null || $raw === '') {
            return null;
        }

        if (!is_numeric($raw)) {
            throw new BadRequestHttpException('classeId doit être un entier.');
        }

        return (int) $raw;
    }

    private function buildStorageKey(string $originalFilename): string
    {
        $base = basename($originalFilename);
        $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $base) ?? 'fichier';
        if ($safe === '' || $safe === '.' || $safe === '..') {
            $safe = 'fichier';
        }

        $prefix = (new \DateTimeImmutable())->format('Y/m');

        return sprintf('devoirs/%s/%s_%s', $prefix, bin2hex(random_bytes(8)), $safe);
    }
}
