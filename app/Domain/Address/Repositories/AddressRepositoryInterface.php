<?php

namespace App\Domain\Address\Repositories;

use App\Domain\Address\Models\Address;
use Illuminate\Database\Eloquent\Collection;

interface AddressRepositoryInterface
{
    public function allByCustomer(int $customerId): Collection;
    public function findByIdAndCustomer(int $id, int $customerId): ?Address;
    public function create(array $data): Address;
    public function update(Address $address, array $data): Address;
    public function delete(Address $address): void;
}
