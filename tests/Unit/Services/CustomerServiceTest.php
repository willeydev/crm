<?php

namespace Tests\Unit\Services;

use App\Application\Services\CustomerService;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Mockery;
use Mockery\MockInterface;
use Tests\Unit\UnitTestCase;

class CustomerServiceTest extends UnitTestCase
{
    private MockInterface $customerRepo;
    private CustomerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepo = Mockery::mock(CustomerRepositoryInterface::class);
        $this->service      = new CustomerService($this->customerRepo);
    }

    // =========================================================
    // listByUser()
    // =========================================================

    public function test_list_returns_customers_of_user(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->customerRepo
            ->shouldReceive('allByUser')
            ->once()
            ->with(1, 15)
            ->andReturn($paginator);

        $result = $this->service->listByUser(1);

        $this->assertSame($paginator, $result);
    }

    // =========================================================
    // findOwned()
    // =========================================================

    public function test_find_owned_returns_customer_when_found(): void
    {
        $customer     = new Customer(['name' => 'João']);
        $customer->id = 5;

        $this->customerRepo
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->with(5, 1)
            ->andReturn($customer);

        $result = $this->service->findOwned(5, 1);

        $this->assertSame($customer, $result);
    }

    public function test_find_owned_throws_404_when_not_found(): void
    {
        $this->customerRepo
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->with(99, 1)
            ->andReturn(null);

        $this->expectException(HttpResponseException::class);

        $this->service->findOwned(99, 1);
    }

    // =========================================================
    // create()
    // =========================================================

    public function test_create_merges_user_id_and_saves(): void
    {
        $customer = new Customer(['name' => 'Maria', 'user_id' => 1]);

        $this->customerRepo
            ->shouldReceive('create')
            ->once()
            ->with(['name' => 'Maria', 'email' => 'maria@test.com', 'user_id' => 1])
            ->andReturn($customer);

        $result = $this->service->create(['name' => 'Maria', 'email' => 'maria@test.com'], 1);

        $this->assertSame($customer, $result);
    }

    // =========================================================
    // update()
    // =========================================================

    public function test_update_finds_owned_and_updates(): void
    {
        $customer     = new Customer(['name' => 'Antigo']);
        $customer->id = 3;
        $updated      = new Customer(['name' => 'Novo']);

        $this->customerRepo
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->with(3, 1)
            ->andReturn($customer);

        $this->customerRepo
            ->shouldReceive('update')
            ->once()
            ->with($customer, ['name' => 'Novo'])
            ->andReturn($updated);

        $result = $this->service->update(3, ['name' => 'Novo'], 1);

        $this->assertSame($updated, $result);
    }

    public function test_update_throws_404_when_customer_not_owned(): void
    {
        $this->customerRepo
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->with(99, 1)
            ->andReturn(null);

        $this->expectException(HttpResponseException::class);

        $this->service->update(99, ['name' => 'Hack'], 1);
    }

    // =========================================================
    // delete()
    // =========================================================

    public function test_delete_finds_owned_and_deletes(): void
    {
        $customer     = new Customer(['name' => 'Para deletar']);
        $customer->id = 7;

        $this->customerRepo
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->with(7, 1)
            ->andReturn($customer);

        $this->customerRepo
            ->shouldReceive('delete')
            ->once()
            ->with($customer);

        $this->service->delete(7, 1);

        $this->assertTrue(true); // chegou aqui = não lançou exceção
    }

    public function test_delete_throws_404_when_customer_not_owned(): void
    {
        $this->customerRepo
            ->shouldReceive('findByIdAndUser')
            ->once()
            ->with(99, 1)
            ->andReturn(null);

        $this->expectException(HttpResponseException::class);

        $this->service->delete(99, 1);
    }
}
