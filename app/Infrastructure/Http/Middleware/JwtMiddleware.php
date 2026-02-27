<?php

namespace App\Infrastructure\Http\Middleware;

use App\Application\Services\AuthService;
use Closure;
use Illuminate\Http\Request;

class JwtMiddleware
{
    public function __construct(private readonly AuthService $authService) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token não fornecido.',
            ], 401);
        }

        $user = $this->authService->getUserFromToken($token);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido ou expirado.',
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }
}
