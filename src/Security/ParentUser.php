<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class ParentUser implements UserInterface
{
    public function __construct(
        private readonly int $parentId,
        private readonly string $telephone,
        private readonly string $nomTuteur,
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

    public function getUserIdentifier(): string
    {
        return (string) $this->parentId;
    }

    public function getRoles(): array
    {
        return ['ROLE_PARENT'];
    }

    public function eraseCredentials(): void
    {
    }
}
