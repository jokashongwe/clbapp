<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class SuperUser implements UserInterface
{
    public function __construct(
        private readonly string $telephone,
    ) {
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getUserIdentifier(): string
    {
        return 'super:'.$this->telephone;
    }

    public function getRoles(): array
    {
        return ['ROLE_SUPER_USER', 'ROLE_DIRECTEUR', 'ROLE_PARENT', 'ROLE_API'];
    }

    public function eraseCredentials(): void
    {
    }
}
