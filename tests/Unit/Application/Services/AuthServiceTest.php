<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\AuthService;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Tests\Unit\UnitTestCase;

class AuthServiceTest extends UnitTestCase
{
    private MockInterface $userRepo;
    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->service  = new AuthService($this->userRepo);
    }

    // =========================================================
    // register()
    // =========================================================

    public function test_register_creates_user_and_returns_token(): void
    {
        $user     = new User(['name' => 'João', 'email' => 'joao@test.com']);
        $user->id = 1;

        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('joao@test.com')
            ->andReturn(null);

        $this->userRepo
            ->shouldReceive('create')
            ->once()
            ->with(['name' => 'João', 'email' => 'joao@test.com', 'password' => 'secret123'])
            ->andReturn($user);

        $result = $this->service->register([
            'name'     => 'João',
            'email'    => 'joao@test.com',
            'password' => 'secret123',
        ]);

        $this->assertSame($user, $result['user']);
        $this->assertNotEmpty($result['token']);
        $this->assertIsString($result['token']);
    }

    public function test_register_throws_exception_when_email_already_exists(): void
    {
        $existing = new User(['email' => 'joao@test.com']);

        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('joao@test.com')
            ->andReturn($existing);

        $this->expectException(\App\Domain\Exceptions\EmailAlreadyExistsException::class);

        $this->service->register([
            'name'     => 'João',
            'email'    => 'joao@test.com',
            'password' => 'secret123',
        ]);
    }

    // =========================================================
    // login()
    // =========================================================

    public function test_login_with_valid_credentials_returns_user_and_token(): void
    {
        $user           = new User(['email' => 'joao@test.com']);
        $user->id       = 1;
        $user->password = Hash::make('secret123');

        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('joao@test.com')
            ->andReturn($user);

        $result = $this->service->login('joao@test.com', 'secret123');

        $this->assertNotNull($result);
        $this->assertSame($user, $result['user']);
        $this->assertIsString($result['token']);
    }

    public function test_login_with_wrong_password_returns_null(): void
    {
        $user           = new User(['email' => 'joao@test.com']);
        $user->password = Hash::make('correta');

        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturn($user);

        $result = $this->service->login('joao@test.com', 'errada');

        $this->assertNull($result);
    }

    public function test_login_with_nonexistent_email_returns_null(): void
    {
        $this->userRepo
            ->shouldReceive('findByEmail')
            ->once()
            ->with('nao@existe.com')
            ->andReturn(null);

        $result = $this->service->login('nao@existe.com', 'qualquer');

        $this->assertNull($result);
    }

    // =========================================================
    // getUserFromToken()
    // =========================================================

    public function test_get_user_from_valid_token_returns_user(): void
    {
        $user     = new User(['name' => 'Test']);
        $user->id = 42;

        $token = JWT::encode([
            'iss' => 'crm-api',
            'sub' => 42,
            'iat' => time(),
            'exp' => time() + 3600,
        ], env('JWT_SECRET'), 'HS256');

        $this->userRepo
            ->shouldReceive('findById')
            ->once()
            ->with(42)
            ->andReturn($user);

        $result = $this->service->getUserFromToken($token);

        $this->assertSame($user, $result);
    }

    public function test_get_user_from_invalid_token_returns_null(): void
    {
        $this->userRepo->shouldNotReceive('findById');

        $result = $this->service->getUserFromToken('token.invalido.aqui');

        $this->assertNull($result);
    }

    public function test_get_user_from_expired_token_returns_null(): void
    {
        $this->userRepo->shouldNotReceive('findById');

        $expiredToken = JWT::encode([
            'iss' => 'crm-api',
            'sub' => 1,
            'iat' => time() - 7200,
            'exp' => time() - 3600, // expirado
        ], env('JWT_SECRET'), 'HS256');

        $result = $this->service->getUserFromToken($expiredToken);

        $this->assertNull($result);
    }
}
