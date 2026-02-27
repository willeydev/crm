<?php

namespace App\Application\Services;

use App\Domain\Exceptions\EmailAlreadyExistsException;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new EmailAlreadyExistsException();
        }

        $user  = $this->userRepository->create($data);
        $token = $this->generateToken($user);

        return ['user' => $user, 'token' => $token];
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return ['user' => $user, 'token' => $this->generateToken($user)];
    }

    public function getUserFromToken(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            return $this->userRepository->findById((int) $decoded->sub);
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateToken(User $user): string
    {
        $ttl = (int) env('JWT_TTL', 1440);

        $payload = [
            'iss' => 'crm-api',
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + ($ttl * 60),
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }
}
