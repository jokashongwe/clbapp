<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Wrapper pour vérifier un hash de PIN avec le PasswordHasher Symfony.
 */
final class PinUser implements PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly string $hashedPassword,
    ) {
    }

    public function getPassword(): string
    {
        return $this->hashedPassword;
    }

    public function getUserIdentifier(): string
    {
        return 'pin';
    }

    public function getRoles(): array
    {
        return [];
    }
}
