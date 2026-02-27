<?php

namespace Tests\Feature;

use App\Domain\Customer\Models\Customer;
use App\Domain\User\Models\User;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    private function authHeader(User $user): array
    {
        $this->post('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $token = json_decode($this->response->getContent(), true)['data']['token'];
        return ['Authorization' => "Bearer {$token}"];
    }

    // =========================================================
    // LIST
    // =========================================================

    public function test_authenticated_user_can_list_own_customers(): void
    {
        $user = User::factory()->create();
        Customer::factory()->count(3)->create(['user_id' => $user->id]);
        Customer::factory()->count(2)->create(); // de outro usuário

        $this->get('/api/v1/customers', $this->authHeader($user));

        $this->assertResponseStatus(200);
        $data = json_decode($this->response->getContent(), true)['data'];
        $this->assertCount(3, $data);
    }

    public function test_list_customers_requires_authentication(): void
    {
        $this->get('/api/v1/customers');
        $this->assertResponseStatus(401);
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function test_can_create_customer(): void
    {
        $user = User::factory()->create();

        $this->post('/api/v1/customers', [
            'name'  => 'Maria Souza',
            'email' => 'maria@example.com',
            'phone' => '(11) 99999-1234',
        ], $this->authHeader($user));

        $this->assertResponseStatus(201);
        $this->seeInDatabase('customers', [
            'name'    => 'Maria Souza',
            'user_id' => $user->id,
        ]);
    }

    public function test_create_customer_requires_name(): void
    {
        $user = User::factory()->create();

        $this->post('/api/v1/customers', ['email' => 'x@x.com'], $this->authHeader($user));

        $this->assertResponseStatus(422);
    }

    // =========================================================
    // SHOW
    // =========================================================

    public function test_can_show_own_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->get("/api/v1/customers/{$customer->id}", $this->authHeader($user));

        $this->assertResponseStatus(200);
        $this->seeJson(['name' => $customer->name]);
    }

    public function test_cannot_show_other_users_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(); // outro usuário

        $this->get("/api/v1/customers/{$customer->id}", $this->authHeader($user));

        $this->assertResponseStatus(404);
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function test_can_update_own_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->put("/api/v1/customers/{$customer->id}", [
            'name' => 'Nome Atualizado',
        ], $this->authHeader($user));

        $this->assertResponseStatus(200);
        $this->seeInDatabase('customers', ['id' => $customer->id, 'name' => 'Nome Atualizado']);
    }

    public function test_cannot_update_other_users_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create();

        $this->put("/api/v1/customers/{$customer->id}", ['name' => 'Hack'], $this->authHeader($user));

        $this->assertResponseStatus(404);
    }

    // =========================================================
    // DELETE
    // =========================================================

    public function test_can_delete_own_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->delete("/api/v1/customers/{$customer->id}", [], $this->authHeader($user));

        $this->assertResponseStatus(204);
        $this->notSeeInDatabase('customers', ['id' => $customer->id]);
    }

    public function test_cannot_delete_other_users_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create();

        $this->delete("/api/v1/customers/{$customer->id}", [], $this->authHeader($user));

        $this->assertResponseStatus(404);
    }
}
