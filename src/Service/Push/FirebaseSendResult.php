<?php

namespace App\Service\Push;

final class FirebaseSendResult
{
    private function __construct(
        private readonly string $status,
        private readonly ?string $error = null,
        private readonly bool $invalidToken = false,
    ) {
    }

    public static function success(): self
    {
        return new self('success');
    }

    public static function skipped(string $reason): self
    {
        return new self('skipped', $reason);
    }

    public static function failed(string $error, bool $invalidToken = false): self
    {
        return new self('failed', $error, $invalidToken);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isInvalidToken(): bool
    {
        return $this->invalidToken;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
