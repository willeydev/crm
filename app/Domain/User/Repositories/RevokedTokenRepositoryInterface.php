<?php

namespace App\Domain\User\Repositories;

use DateTimeInterface;

interface RevokedTokenRepositoryInterface
{
    public function revoke(string $tokenHash, DateTimeInterface $expiresAt): void;
    public function isRevoked(string $tokenHash): bool;
}
