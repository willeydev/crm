<?php

namespace Tests\Feature;

use App\Domain\User\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    // =========================================================
    // REGISTER
    // =========================================================

    public function test_user_can_register_with_valid_data(): void
    {
        $this->post('/api/v1/auth/register', [
            'name'                  => 'João Silva',
            'email'                 => 'joao@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertResponseStatus(201);
        $this->seeJsonStructure([
            'success',
            'message',
            'data' => ['user', 'token'],
        ]);
        $this->seeInDatabase('users', ['email' => 'joao@example.com']);
    }

    public function test_register_fails_with_invalid_email(): void
    {
        $this->post('/api/v1/auth/register', [
            'name'                  => 'João',
            'email'                 => 'not-an-email',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertResponseStatus(422);
        $this->seeJson(['success' => false]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicado@example.com']);

        $this->post('/api/v1/auth/register', [
            'name'                  => 'Outro',
            'email'                 => 'duplicado@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertResponseStatus(409);
        $this->seeJson(['success' => false, 'message' => 'E-mail já cadastrado.']);
    }

    public function test_register_fails_when_passwords_dont_match(): void
    {
        $this->post('/api/v1/auth/register', [
            'name'                  => 'Teste',
            'email'                 => 'teste@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different',
        ]);

        $this->assertResponseStatus(422);
    }

    // =========================================================
    // LOGIN
    // =========================================================

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/api/v1/auth/login', [
            'email'    => 'login@example.com',
            'password' => 'password123',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'success',
            'data' => ['user', 'token'],
        ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $this->post('/api/v1/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong_password',
        ]);

        $this->assertResponseStatus(401);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->post('/api/v1/auth/login', [
            'email'    => 'naoexiste@example.com',
            'password' => 'anypassword',
        ]);

        $this->assertResponseStatus(401);
    }

    // =========================================================
    // ME / LOGOUT
    // =========================================================

    public function test_me_returns_authenticated_user(): void
    {
        $user  = User::factory()->create();
        $token = $this->getTokenFor($user);

        $this->get('/api/v1/auth/me', ['Authorization' => "Bearer {$token}"]);

        $this->assertResponseStatus(200);
        $this->seeJson(['success' => true]);
    }

    public function test_me_fails_without_token(): void
    {
        $this->get('/api/v1/auth/me');
        $this->assertResponseStatus(401);
    }

    public function test_logout_returns_success(): void
    {
        $user  = User::factory()->create();
        $token = $this->getTokenFor($user);

        $this->post('/api/v1/auth/logout', [], ['Authorization' => "Bearer {$token}"]);

        $this->assertResponseStatus(200);
        $this->seeJson(['success' => true]);
    }

    // =========================================================
    // Helper
    // =========================================================

    private function getTokenFor(User $user): string
    {
        $this->post('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        return json_decode($this->response->getContent(), true)['data']['token'];
    }
}
