<?php

namespace App\Providers;

use App\Application\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app['auth']->viaRequest('jwt', function ($request) {
            $header = $request->header('Authorization', '');

            if (! str_starts_with($header, 'Bearer ')) {
                return null;
            }

            $token = substr($header, 7);

            /** @var AuthService $authService */
            $authService = $this->app->make(AuthService::class);

            return $authService->getUserFromToken($token);
        });
    }
}
