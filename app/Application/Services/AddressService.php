<?php

namespace App\Application\Services;

use App\Domain\Address\Models\Address;
use App\Domain\Address\Repositories\AddressRepositoryInterface;
use App\Domain\Cep\CepGatewayInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddressService
{
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly CustomerService $customerService,
        private readonly CepGatewayInterface $cepGateway
    ) {}

    public function listByCustomer(int $customerId, int $userId): Collection
    {
        $this->customerService->findOwned($customerId, $userId);
        return $this->addressRepository->allByCustomer($customerId);
    }

    public function findOwned(int $addressId, int $customerId, int $userId): Address
    {
        $this->customerService->findOwned($customerId, $userId);

        $address = $this->addressRepository->findByIdAndCustomer($addressId, $customerId);

        if (! $address) {
            $this->abortNotFound();
        }

        return $address;
    }

    public function create(array $data, int $customerId, int $userId): Address
    {
        $this->customerService->findOwned($customerId, $userId);

        $cepData = $this->cepGateway->lookup($data['cep']);

        if (! $cepData) {
            $this->abortInvalidCep();
        }

        return $this->addressRepository->create(array_merge($cepData, [
            'customer_id' => $customerId,
            'number'      => $data['number'],
            'complement'  => $data['complement'] ?? null,
        ]));
    }

    public function update(int $addressId, array $data, int $customerId, int $userId): Address
    {
        $address = $this->findOwned($addressId, $customerId, $userId);

        if (isset($data['cep'])) {
            $cepData = $this->cepGateway->lookup($data['cep']);

            if (! $cepData) {
                $this->abortInvalidCep();
            }

            $data = array_merge($data, $cepData);
        }

        return $this->addressRepository->update($address, $data);
    }

    public function delete(int $addressId, int $customerId, int $userId): void
    {
        $address = $this->findOwned($addressId, $customerId, $userId);
        $this->addressRepository->delete($address);
    }

    private function abortNotFound(): never
    {
        throw new HttpResponseException(
            response()->json(['success' => false, 'message' => 'Endereço não encontrado.'], 404)
        );
    }

    private function abortInvalidCep(): never
    {
        throw new HttpResponseException(
            response()->json(['success' => false, 'message' => 'CEP não encontrado.'], 422)
        );
    }
}
