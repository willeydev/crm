<?php

namespace App\Domain\Customer\Repositories;

use App\Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function allByUser(int $userId): Collection;
    public function findByIdAndUser(int $id, int $userId): ?Customer;
    public function create(array $data): Customer;
    public function update(Customer $customer, array $data): Customer;
    public function delete(Customer $customer): void;
}
