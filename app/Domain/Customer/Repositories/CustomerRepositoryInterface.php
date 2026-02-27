<?php

namespace App\Domain\Customer\Repositories;

use App\Domain\Customer\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function allByUser(int $userId, int $perPage): LengthAwarePaginator;
    public function findByIdAndUser(int $id, int $userId): ?Customer;
    public function create(array $data): Customer;
    public function update(Customer $customer, array $data): Customer;
    public function delete(Customer $customer): void;
}
