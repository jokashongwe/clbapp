<?php

namespace App\Service;

use App\Entity\ParentPin;
use App\Entity\ParentSession;
use App\Entity\ParentEleve;
use App\Repository\ParentEleveRepository;
use App\Repository\ParentPinRepository;
use App\Repository\ParentSessionRepository;
use App\Security\PinUser;
use App\Util\PhoneNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class ParentAuthService
{
    public function __construct(
        private readonly ParentEleveRepository $parentEleveRepository,
        private readonly ParentPinRepository $parentPinRepository,
        private readonly ParentSessionRepository $parentSessionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        #[Autowire(env: 'DEFAULT_PIN')]
        private readonly string $defaultPin,
        #[Autowire(env: 'int:API_SESSION_TTL')]
        private readonly int $sessionTtl,
    ) {
    }

    public function login(string $telephone, string $pin): array
    {
        $parent = $this->findActiveParentByTelephone($telephone);
        $this->assertPinValid($parent, $pin);

        return $this->createSessionResponse($parent);
    }

    public function changePin(string $telephone, string $currentPin, string $newPin): void
    {
        $this->assertPinFormat($newPin);

        $parent = $this->findActiveParentByTelephone($telephone);
        $this->assertPinValid($parent, $currentPin);

        $normalizedPhone = PhoneNormalizer::normalize($telephone);
        $hasher = $this->passwordHasherFactory->getPasswordHasher(PinUser::class);

        $parentPin = $this->parentPinRepository->findOneByParent($parent);
        if ($parentPin === null) {
            $parentPin = new ParentPin();
            $parentPin->setParent($parent);
            $this->entityManager->persist($parentPin);
        }

        $parentPin->setTelephone($normalizedPhone);
        $parentPin->setPinHash($hasher->hash($newPin));
        $parentPin->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function findActiveParentByTelephone(string $telephone): ParentEleve
    {
        $normalized = PhoneNormalizer::normalize($telephone);
        if ($normalized === '') {
            throw new BadRequestHttpException('Numéro de téléphone invalide.');
        }

        $parentIds = $this->parentEleveRepository->findActiveIdsByTelephoneTuteur($normalized);
        if ($parentIds === []) {
            throw new UnauthorizedHttpException('', 'Aucun tuteur actif trouvé pour ce numéro de téléphone.');
        }

        if (\count($parentIds) > 1) {
            throw new UnauthorizedHttpException('', 'Ce numéro est associé à plusieurs tuteurs. Contactez l\'école.');
        }

        $parent = $this->parentEleveRepository->find($parentIds[0]);
        if ($parent === null || $parent->isSupp()) {
            throw new UnauthorizedHttpException('', 'Aucun tuteur actif trouvé pour ce numéro de téléphone.');
        }

        if (!$this->parentEleveRepository->telephoneMatchesParent($parent, $normalized)) {
            throw new UnauthorizedHttpException('', 'Le numéro ne correspond pas au tuteur enregistré.');
        }

        return $parent;
    }

    private function assertPinValid(ParentEleve $parent, string $pin): void
    {
        if (!$this->isPinValid($parent, $pin)) {
            throw new UnauthorizedHttpException('', 'Téléphone ou PIN incorrect.');
        }
    }

    private function isPinValid(ParentEleve $parent, string $pin): bool
    {
        $parentPin = $this->parentPinRepository->findOneByParent($parent);

        if ($parentPin !== null) {
            $hasher = $this->passwordHasherFactory->getPasswordHasher(PinUser::class);

            return $hasher->verify($parentPin->getPinHash(), $pin);
        }

        return $this->defaultPin !== '' && hash_equals($this->defaultPin, $pin);
    }

    private function createSessionResponse(ParentEleve $parent): array
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify('+'.$this->sessionTtl.' seconds');

        $session = new ParentSession();
        $session->setParent($parent);
        $session->setTokenHash($tokenHash);
        $session->setCreatedAt($now);
        $session->setExpiresAt($expiresAt);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return [
            'token' => $token,
            'expires_at' => $expiresAt->format(\DateTimeInterface::ATOM),
            'parent' => [
                'id' => $parent->getId(),
                'nomTuteur' => $parent->getNomTuteur(),
                'numeroTelephoneTuteur' => $parent->getNumeroTelephoneTuteur(),
            ],
        ];
    }

    private function assertPinFormat(string $pin): void
    {
        if (!preg_match('/^\d{4,6}$/', $pin)) {
            throw new BadRequestHttpException('Le PIN doit contenir entre 4 et 6 chiffres.');
        }
    }
}
