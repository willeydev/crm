<?php

namespace Tests\Unit\Services;

use App\Application\Services\AddressService;
use App\Application\Services\CustomerService;
use App\Domain\Address\Models\Address;
use App\Domain\Address\Repositories\AddressRepositoryInterface;
use App\Domain\Cep\CepGatewayInterface;
use App\Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\Unit\UnitTestCase;

class AddressServiceTest extends UnitTestCase
{
    private MockInterface $addressRepo;
    private MockInterface $customerService;
    private MockInterface $cepGateway;
    private AddressService $service;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addressRepo     = Mockery::mock(AddressRepositoryInterface::class);
        $this->customerService = Mockery::mock(CustomerService::class);
        $this->cepGateway      = Mockery::mock(CepGatewayInterface::class);

        $this->service = new AddressService(
            $this->addressRepo,
            $this->customerService,
            $this->cepGateway
        );

        $this->customer     = new Customer();
        $this->customer->id = 1;
    }

    private function fakeCepData(): array
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

    // =========================================================
    // listByCustomer()
    // =========================================================

    public function test_list_verifies_ownership_and_returns_addresses(): void
    {
        $collection = new Collection([new Address(), new Address()]);

        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('allByCustomer')
            ->once()
            ->with(1)
            ->andReturn($collection);

        $result = $this->service->listByCustomer(1, 10);

        $this->assertCount(2, $result);
        $this->assertSame($collection, $result);
    }

    public function test_list_throws_404_when_customer_not_owned(): void
    {
        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(99, 10)
            ->andThrow(new HttpResponseException(new JsonResponse(['success' => false], 404)));

        $this->expectException(HttpResponseException::class);

        $this->service->listByCustomer(99, 10);
    }

    // =========================================================
    // findOwned()
    // =========================================================

    public function test_find_owned_returns_address_when_found(): void
    {
        $address     = new Address();
        $address->id = 5;

        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('findByIdAndCustomer')
            ->once()
            ->with(5, 1)
            ->andReturn($address);

        $result = $this->service->findOwned(5, 1, 10);

        $this->assertSame($address, $result);
    }

    public function test_find_owned_throws_404_when_address_not_found(): void
    {
        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('findByIdAndCustomer')
            ->once()
            ->with(99, 1)
            ->andReturn(null);

        $this->expectException(HttpResponseException::class);

        $this->service->findOwned(99, 1, 10);
    }

    // =========================================================
    // create()
    // =========================================================

    public function test_create_looks_up_cep_and_saves_address(): void
    {
        $cepData = $this->fakeCepData();
        $address = new Address();

        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->cepGateway
            ->shouldReceive('lookup')
            ->once()
            ->with('01310100')
            ->andReturn($cepData);

        $this->addressRepo
            ->shouldReceive('create')
            ->once()
            ->with(array_merge($cepData, ['customer_id' => 1, 'number' => '1000', 'complement' => null]))
            ->andReturn($address);

        $result = $this->service->create(['cep' => '01310100', 'number' => '1000'], 1, 10);

        $this->assertSame($address, $result);
    }

    public function test_create_throws_422_when_cep_not_found(): void
    {
        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->cepGateway
            ->shouldReceive('lookup')
            ->once()
            ->with('00000000')
            ->andReturn(null);

        $this->expectException(HttpResponseException::class);

        $this->service->create(['cep' => '00000000', 'number' => '1'], 1, 10);
    }

    public function test_create_throws_404_when_customer_not_owned(): void
    {
        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(99, 10)
            ->andThrow(new HttpResponseException(new JsonResponse(['success' => false], 404)));

        $this->expectException(HttpResponseException::class);

        $this->service->create(['cep' => '01310100', 'number' => '1'], 99, 10);
    }

    // =========================================================
    // update()
    // =========================================================

    public function test_update_without_new_cep_updates_only_given_fields(): void
    {
        $address     = new Address();
        $address->id = 5;
        $updated     = new Address();

        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('findByIdAndCustomer')
            ->once()
            ->with(5, 1)
            ->andReturn($address);

        $this->cepGateway->shouldNotReceive('lookup');

        $this->addressRepo
            ->shouldReceive('update')
            ->once()
            ->with($address, ['number' => '999'])
            ->andReturn($updated);

        $result = $this->service->update(5, ['number' => '999'], 1, 10);

        $this->assertSame($updated, $result);
    }

    public function test_update_with_new_cep_fetches_and_merges_data(): void
    {
        $cepData = $this->fakeCepData();
        $address     = new Address();
        $address->id = 5;
        $updated     = new Address();

        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('findByIdAndCustomer')
            ->once()
            ->with(5, 1)
            ->andReturn($address);

        $this->cepGateway
            ->shouldReceive('lookup')
            ->once()
            ->with('01310100')
            ->andReturn($cepData);

        $this->addressRepo
            ->shouldReceive('update')
            ->once()
            ->with($address, array_merge(['cep' => '01310100', 'number' => '200'], $cepData))
            ->andReturn($updated);

        $result = $this->service->update(5, ['cep' => '01310100', 'number' => '200'], 1, 10);

        $this->assertSame($updated, $result);
    }

    public function test_update_throws_422_when_new_cep_not_found(): void
    {
        $address     = new Address();
        $address->id = 5;

        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('findByIdAndCustomer')
            ->once()
            ->with(5, 1)
            ->andReturn($address);

        $this->cepGateway
            ->shouldReceive('lookup')
            ->once()
            ->with('00000000')
            ->andReturn(null);

        $this->expectException(HttpResponseException::class);

        $this->service->update(5, ['cep' => '00000000'], 1, 10);
    }

    // =========================================================
    // delete()
    // =========================================================

    public function test_delete_finds_owned_and_deletes(): void
    {
        $address     = new Address();
        $address->id = 5;

        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('findByIdAndCustomer')
            ->once()
            ->with(5, 1)
            ->andReturn($address);

        $this->addressRepo
            ->shouldReceive('delete')
            ->once()
            ->with($address);

        $this->service->delete(5, 1, 10);

        $this->assertTrue(true);
    }

    public function test_delete_throws_404_when_address_not_found(): void
    {
        $this->customerService
            ->shouldReceive('findOwned')
            ->once()
            ->with(1, 10)
            ->andReturn($this->customer);

        $this->addressRepo
            ->shouldReceive('findByIdAndCustomer')
            ->once()
            ->with(99, 1)
            ->andReturn(null);

        $this->expectException(HttpResponseException::class);

        $this->service->delete(99, 1, 10);
    }
}
