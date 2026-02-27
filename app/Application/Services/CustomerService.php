<?php

namespace App\Application\Services;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository
    ) {}

    public function listByUser(int $userId): Collection
    {
        return $this->customerRepository->allByUser($userId);
    }

    public function findOwned(int $id, int $userId): Customer
    {
        $customer = $this->customerRepository->findByIdAndUser($id, $userId);

        if (! $customer) {
            $this->abortNotFound();
        }

        return $customer;
    }

    public function create(array $data, int $userId): Customer
    {
        return $this->customerRepository->create(array_merge($data, ['user_id' => $userId]));
    }

    public function update(int $id, array $data, int $userId): Customer
    {
        $customer = $this->findOwned($id, $userId);
        return $this->customerRepository->update($customer, $data);
    }

    public function delete(int $id, int $userId): void
    {
        $customer = $this->findOwned($id, $userId);
        $this->customerRepository->delete($customer);
    }

    private function abortNotFound(): never
    {
        throw new HttpResponseException(
            response()->json(['success' => false, 'message' => 'Cliente não encontrado.'], 404)
        );
    }
}
