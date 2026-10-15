<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Repository\AnnonceLectureRepository;
use App\Repository\AnnonceRepository;
use App\Repository\EleveRepository;
use App\Repository\ParentEleveRepository;
use App\Security\ParentUser;
use App\Service\Push\ParentPushNotificationService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/annonces')]
final class AnnonceController extends AbstractApiController
{
    #[Route('', name: 'api_annonces_create', methods: ['POST'])]
    #[IsGranted('ROLE_DIRECTEUR')]
    public function create(
        Request $request,
        AnnonceRepository $annonceRepository,
        ParentEleveRepository $parentEleveRepository,
        EleveRepository $eleveRepository,
        Connection $connection,
        ParentPushNotificationService $pushNotificationService,
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof ParentUser) {
            throw new AccessDeniedHttpException('Création réservée à un compte parent directeur.');
        }

        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('Corps JSON invalide.');
        }

        $type = $payload['type'] ?? null;
        $titre = isset($payload['titre']) ? trim((string) $payload['titre']) : '';
        $contenu = isset($payload['contenu']) ? trim((string) $payload['contenu']) : '';
        $cibleType = $payload['cibleType'] ?? $payload['cible_type'] ?? null;
        $cibleId = $payload['cibleId'] ?? $payload['cible_id'] ?? null;

        if (!\is_string($type) || !\in_array($type, Annonce::TYPES, true)) {
            throw new BadRequestHttpException(
                'Le champ type est requis (Information, Rappel ou Convocation).',
            );
        }

        if ($titre === '') {
            throw new BadRequestHttpException('Le champ titre est requis.');
        }

        if ($contenu === '') {
            throw new BadRequestHttpException('Le champ contenu est requis.');
        }

        if (!\is_string($cibleType) || !\in_array($cibleType, Annonce::CIBLE_TYPES, true)) {
            throw new BadRequestHttpException(
                'Le champ cibleType est requis (tous, eleve, classe ou option).',
            );
        }

        $cibleIdInt = null;
        if ($cibleType !== Annonce::CIBLE_TOUS) {
            if ($cibleId === null || $cibleId === '') {
                throw new BadRequestHttpException('cibleId est requis pour ce type de ciblage.');
            }
            if (!is_numeric($cibleId)) {
                throw new BadRequestHttpException('cibleId doit être un entier.');
            }
            $cibleIdInt = (int) $cibleId;
            $this->assertCibleExists($cibleType, $cibleIdInt, $eleveRepository, $connection);
        }

        $parent = $parentEleveRepository->find($user->getParentId());
        if ($parent === null || $parent->isSupp()) {
            throw new NotFoundHttpException('Parent introuvable.');
        }

        $annonce = (new Annonce())
            ->setType($type)
            ->setTitre($titre)
            ->setContenu($contenu)
            ->setCibleType($cibleType)
            ->setCibleId($cibleIdInt)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreatedBy($parent)
            ->setSupp(false);

        $em = $annonceRepository->getEntityManager();
        $em->persist($annonce);
        $em->flush();

        $pushNotificationService->notifyNewAnnonce($annonce);

        return $this->jsonRead($annonce, 201);
    }

    #[Route('', name: 'api_annonces_unread', methods: ['GET'])]
    public function unread(AnnonceRepository $annonceRepository): JsonResponse
    {
        $parentId = $this->requireParentId();

        return $this->jsonRead($annonceRepository->findUnreadForParent($parentId));
    }

    #[Route('/{id}/read', name: 'api_annonces_read', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function read(
        int $id,
        AnnonceRepository $annonceRepository,
        AnnonceLectureRepository $lectureRepository,
        ParentEleveRepository $parentEleveRepository,
    ): JsonResponse {
        $parentId = $this->requireParentId();

        $annonce = $annonceRepository->find($id);
        if ($annonce === null || $annonce->isSupp()) {
            throw new NotFoundHttpException('Annonce introuvable.');
        }

        if (!$annonceRepository->isVisibleForParent($annonce, $parentId)) {
            throw new NotFoundHttpException('Annonce introuvable.');
        }

        $parent = $parentEleveRepository->find($parentId);
        if ($parent === null) {
            throw new NotFoundHttpException('Parent introuvable.');
        }

        $lectureRepository->markAsRead($annonce, $parent);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/read-all', name: 'api_annonces_read_all', methods: ['POST'])]
    public function readAll(AnnonceRepository $annonceRepository): JsonResponse
    {
        $parentId = $this->requireParentId();
        $marked = $annonceRepository->markAllUnreadAsReadForParent($parentId);

        return new JsonResponse([
            'ok' => true,
            'markedCount' => $marked,
        ]);
    }

    private function requireParentId(): int
    {
        $user = $this->getUser();
        if (!$user instanceof ParentUser) {
            throw new AccessDeniedHttpException('Réservé à un parent authentifié (session login).');
        }

        return $user->getParentId();
    }

    private function assertCibleExists(
        string $cibleType,
        int $cibleId,
        EleveRepository $eleveRepository,
        Connection $connection,
    ): void {
        if ($cibleType === Annonce::CIBLE_ELEVE) {
            $eleve = $eleveRepository->find($cibleId);
            if ($eleve === null || $eleve->isSupp()) {
                throw new BadRequestHttpException('Élève cible introuvable.');
            }

            return;
        }

        if ($cibleType === Annonce::CIBLE_CLASSE) {
            $exists = $connection->fetchOne('SELECT 1 FROM classe WHERE id = :id', ['id' => $cibleId]);
            if ($exists === false) {
                throw new BadRequestHttpException('Classe cible introuvable.');
            }

            return;
        }

        if ($cibleType === Annonce::CIBLE_OPTION) {
            $exists = $connection->fetchOne('SELECT 1 FROM categorie WHERE id = :id', ['id' => $cibleId]);
            if ($exists === false) {
                throw new BadRequestHttpException('Option (catégorie) cible introuvable.');
            }
        }
    }
}
