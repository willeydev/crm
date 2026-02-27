<?php

namespace Tests\Feature;

use App\Domain\Cep\CepGatewayInterface;
use App\Domain\Customer\Models\Customer;
use App\Domain\User\Models\User;
use Tests\TestCase;

class AddressTest extends TestCase
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

    private function mockCep(): void
    {
        $this->app->instance(CepGatewayInterface::class, new class implements CepGatewayInterface {
            public function lookup(string $cep): ?array
            {
                return [
                    'cep'          => '01310100',
                    'street'       => 'Avenida Paulista',
                    'neighborhood' => 'Bela Vista',
                    'city'         => 'São Paulo',
                    'state'        => 'SP',
                    'country'      => 'BR',
                ];
            }
        });
    }

    // =========================================================
    // LIST
    // =========================================================

    public function test_can_list_addresses_of_own_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        // Criar 3 endereços via factory (sem chamar CEP externo)
        \App\Domain\Address\Models\Address::factory()->count(3)->create(['customer_id' => $customer->id]);

        $this->get("/api/v1/customers/{$customer->id}/addresses", $this->authHeader($user));

        $this->assertResponseStatus(200);
        $data = json_decode($this->response->getContent(), true)['data'];
        $this->assertCount(3, $data);
    }

    public function test_cannot_list_addresses_of_other_users_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(); // outro usuário

        $this->get("/api/v1/customers/{$customer->id}/addresses", $this->authHeader($user));

        $this->assertResponseStatus(404);
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function test_can_create_address_with_valid_cep(): void
    {
        $this->mockCep();

        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->post("/api/v1/customers/{$customer->id}/addresses", [
            'cep'    => '01310100',
            'number' => '1000',
        ], $this->authHeader($user));

        $this->assertResponseStatus(201);
        $this->seeInDatabase('addresses', [
            'customer_id' => $customer->id,
            'cep'         => '01310100',
            'street'      => 'Avenida Paulista',
        ]);
    }

    public function test_create_address_fails_with_invalid_cep(): void
    {
        $this->app->instance(CepGatewayInterface::class, new class implements CepGatewayInterface {
            public function lookup(string $cep): ?array { return null; }
        });

        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->post("/api/v1/customers/{$customer->id}/addresses", [
            'cep'    => '00000000',
            'number' => '1',
        ], $this->authHeader($user));

        $this->assertResponseStatus(422);
    }

    public function test_create_address_requires_number(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->post("/api/v1/customers/{$customer->id}/addresses", [
            'cep' => '01310100',
        ], $this->authHeader($user));

        $this->assertResponseStatus(422);
    }

    public function test_cannot_create_address_for_other_users_customer(): void
    {
        $this->mockCep();

        $user     = User::factory()->create();
        $customer = Customer::factory()->create(); // outro usuário

        $this->post("/api/v1/customers/{$customer->id}/addresses", [
            'cep'    => '01310100',
            'number' => '1',
        ], $this->authHeader($user));

        $this->assertResponseStatus(404);
    }

    // =========================================================
    // SHOW
    // =========================================================

    public function test_can_show_own_address(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $address  = \App\Domain\Address\Models\Address::factory()->create(['customer_id' => $customer->id]);

        $this->get("/api/v1/customers/{$customer->id}/addresses/{$address->id}", $this->authHeader($user));

        $this->assertResponseStatus(200);
        $this->seeJson(['id' => $address->id]);
    }

    public function test_cannot_show_address_of_other_users_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create();
        $address  = \App\Domain\Address\Models\Address::factory()->create(['customer_id' => $customer->id]);

        $this->get("/api/v1/customers/{$customer->id}/addresses/{$address->id}", $this->authHeader($user));

        $this->assertResponseStatus(404);
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function test_can_update_address_number(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $address  = \App\Domain\Address\Models\Address::factory()->create(['customer_id' => $customer->id]);

        $this->put("/api/v1/customers/{$customer->id}/addresses/{$address->id}", [
            'number' => '999',
        ], $this->authHeader($user));

        $this->assertResponseStatus(200);
        $this->seeInDatabase('addresses', ['id' => $address->id, 'number' => '999']);
    }

    public function test_cannot_update_address_of_other_users_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create();
        $address  = \App\Domain\Address\Models\Address::factory()->create(['customer_id' => $customer->id]);

        $this->put("/api/v1/customers/{$customer->id}/addresses/{$address->id}", [
            'number' => '999',
        ], $this->authHeader($user));

        $this->assertResponseStatus(404);
    }

    // =========================================================
    // DELETE
    // =========================================================

    public function test_can_delete_own_address(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);
        $address  = \App\Domain\Address\Models\Address::factory()->create(['customer_id' => $customer->id]);

        $this->delete("/api/v1/customers/{$customer->id}/addresses/{$address->id}", [], $this->authHeader($user));

        $this->assertResponseStatus(204);
        $this->notSeeInDatabase('addresses', ['id' => $address->id]);
    }

    public function test_cannot_delete_address_of_other_users_customer(): void
    {
        $user     = User::factory()->create();
        $customer = Customer::factory()->create();
        $address  = \App\Domain\Address\Models\Address::factory()->create(['customer_id' => $customer->id]);

        $this->delete("/api/v1/customers/{$customer->id}/addresses/{$address->id}", [], $this->authHeader($user));

        $this->assertResponseStatus(404);
    }
}
