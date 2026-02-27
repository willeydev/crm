<?php

namespace App\Domain\Customer\Repositories;

use App\Domain\Customer\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function allByUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return Customer::where('user_id', $userId)->paginate($perPage);
    }

    public function findByIdAndUser(int $id, int $userId): ?Customer
    {
        return Customer::where('id', $id)->where('user_id', $userId)->first();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
