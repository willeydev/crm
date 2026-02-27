<?php

namespace App\Providers;

use App\Domain\Cep\CepGatewayInterface;
use App\Domain\Customer\Repositories\CustomerRepository;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Gateways\BrasilApiCepGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(CepGatewayInterface::class, BrasilApiCepGateway::class);
    }
}
