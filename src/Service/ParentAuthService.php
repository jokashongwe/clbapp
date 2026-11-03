<?php

namespace App\Service;

use App\Entity\ParentPin;
use App\Entity\ParentSession;
use App\Entity\ParentEleve;
use App\Entity\SuperUserPin;
use App\Entity\SuperUserSession;
use App\Repository\ParentDirecteurRepository;
use App\Repository\ParentEleveRepository;
use App\Repository\ParentPinRepository;
use App\Repository\ParentSessionRepository;
use App\Repository\SuperUserPinRepository;
use App\Repository\SuperUserSessionRepository;
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
        private readonly ParentDirecteurRepository $parentDirecteurRepository,
        private readonly ParentPinRepository $parentPinRepository,
        private readonly ParentSessionRepository $parentSessionRepository,
        private readonly SuperUserPhoneRegistry $superUserPhoneRegistry,
        private readonly SuperUserPinRepository $superUserPinRepository,
        private readonly SuperUserSessionRepository $superUserSessionRepository,
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
        $telephone = trim($telephone);
        $pin = trim($pin);

        // Les numéros listés dans SUPER_USER_PHONES priment toujours sur parent_eleve.
        if ($this->superUserPhoneRegistry->isSuperUser($telephone)) {
            $this->assertSuperUserPinValid($telephone, $pin);

            return $this->createSuperUserSessionResponse($telephone);
        }

        $parent = $this->findActiveParentByTelephoneOrNull($telephone);
        if ($parent !== null) {
            $this->assertPinValid($parent, $pin);

            return $this->createSessionResponse($parent);
        }

        throw new UnauthorizedHttpException(
            '',
            'Aucun tuteur actif ni super utilisateur trouvé pour ce numéro de téléphone.',
        );
    }

    public function changePin(string $telephone, string $currentPin, string $newPin): void
    {
        $telephone = trim($telephone);
        $currentPin = trim($currentPin);
        $newPin = trim($newPin);
        $this->assertPinFormat($newPin);

        if ($this->superUserPhoneRegistry->isSuperUser($telephone)) {
            $this->assertSuperUserPinValid($telephone, $currentPin);

            $normalizedPhone = PhoneNormalizer::toNational($telephone);
            $hasher = $this->passwordHasherFactory->getPasswordHasher(PinUser::class);

            $superPin = $this->superUserPinRepository->findOneByTelephone($normalizedPhone);
            if ($superPin === null) {
                $superPin = new SuperUserPin();
                $superPin->setTelephone($normalizedPhone);
                $this->entityManager->persist($superPin);
            }

            $superPin->setPinHash($hasher->hash($newPin));
            $superPin->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return;
        }

        $parent = $this->findActiveParentByTelephoneOrNull($telephone);
        if ($parent !== null) {
            $this->assertPinValid($parent, $currentPin);

            $normalizedPhone = PhoneNormalizer::toNational($telephone);
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

            return;
        }

        throw new UnauthorizedHttpException('', 'Aucun tuteur actif ni super utilisateur trouvé pour ce numéro de téléphone.');
    }

    public function findActiveParentByTelephone(string $telephone): ParentEleve
    {
        $parent = $this->findActiveParentByTelephoneOrNull($telephone);
        if ($parent === null) {
            throw new UnauthorizedHttpException('', 'Aucun tuteur actif trouvé pour ce numéro de téléphone.');
        }

        return $parent;
    }

    private function findActiveParentByTelephoneOrNull(string $telephone): ?ParentEleve
    {
        $normalized = PhoneNormalizer::normalize($telephone);
        if ($normalized === '') {
            throw new BadRequestHttpException('Numéro de téléphone invalide.');
        }

        $parentIds = $this->parentEleveRepository->findActiveIdsByTelephoneTuteur($telephone);
        if ($parentIds === []) {
            return null;
        }

        $parent = $this->parentEleveRepository->find($parentIds[0]);
        if ($parent === null || $parent->isSupp()) {
            return null;
        }

        if (!$this->parentEleveRepository->telephoneMatchesParent($parent, $telephone)) {
            return null;
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

    private function assertSuperUserPinValid(string $telephone, string $pin): void
    {
        if (!$this->isSuperUserPinValid($telephone, $pin)) {
            throw new UnauthorizedHttpException('', 'Téléphone ou PIN incorrect.');
        }
    }

    private function isSuperUserPinValid(string $telephone, string $pin): bool
    {
        $normalizedPhone = PhoneNormalizer::toNational($telephone);
        $superPin = $this->superUserPinRepository->findOneByTelephone($normalizedPhone);

        if ($superPin !== null) {
            $hasher = $this->passwordHasherFactory->getPasswordHasher(PinUser::class);

            return $hasher->verify($superPin->getPinHash(), $pin);
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

        $isDirecteur = $this->parentDirecteurRepository->isDirecteur($parent->getId());
        $roles = $isDirecteur ? ['ROLE_DIRECTEUR', 'ROLE_PARENT'] : ['ROLE_PARENT'];

        return [
            'token' => $token,
            'expires_at' => $expiresAt->format(\DateTimeInterface::ATOM),
            'roles' => $roles,
            'isSuperUser' => false,
            'parent' => [
                'id' => $parent->getId(),
                'nomTuteur' => $parent->getNomTuteur(),
                'numeroTelephoneTuteur' => $parent->getNumeroTelephoneTuteur(),
                'isDirecteur' => $isDirecteur,
            ],
        ];
    }

    private function createSuperUserSessionResponse(string $telephone): array
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $now = new \DateTimeImmutable();
        $expiresAt = $now->modify('+'.$this->sessionTtl.' seconds');
        $normalizedPhone = PhoneNormalizer::toNational($telephone);

        $session = new SuperUserSession();
        $session->setTelephone($normalizedPhone);
        $session->setTokenHash($tokenHash);
        $session->setCreatedAt($now);
        $session->setExpiresAt($expiresAt);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return [
            'token' => $token,
            'expires_at' => $expiresAt->format(\DateTimeInterface::ATOM),
            'roles' => ['ROLE_SUPER_USER', 'ROLE_DIRECTEUR', 'ROLE_PARENT'],
            'isSuperUser' => true,
            'parent' => null,
            'superUser' => [
                'numeroTelephone' => $normalizedPhone,
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
