<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class ParentUser implements UserInterface
{
    public function __construct(
        private readonly int $parentId,
        private readonly string $telephone,
        private readonly string $nomTuteur,
        private readonly bool $isDirecteur = false,
    ) {
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getNomTuteur(): string
    {
        return $this->nomTuteur;
    }

    public function isDirecteur(): bool
    {
        return $this->isDirecteur;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->parentId;
    }

    public function getRoles(): array
    {
        if ($this->isDirecteur) {
            return ['ROLE_DIRECTEUR', 'ROLE_PARENT', 'ROLE_API'];
        }

        return ['ROLE_PARENT', 'ROLE_API'];
    }

    public function eraseCredentials(): void
    {
    }
}
