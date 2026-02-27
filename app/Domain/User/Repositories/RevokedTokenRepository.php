<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\RevokedToken;
use DateTimeInterface;
use Illuminate\Support\Facades\Date;

class RevokedTokenRepository implements RevokedTokenRepositoryInterface
{
    public function revoke(string $tokenHash, DateTimeInterface $expiresAt): void
    {
        RevokedToken::create([
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public function isRevoked(string $tokenHash): bool
    {
        return RevokedToken::where('token_hash', $tokenHash)
            ->where('expires_at', '>', Date::now())
            ->exists();
    }
}
