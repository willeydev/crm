<?php

namespace App\Domain\Address\Repositories;

use App\Domain\Address\Models\Address;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository implements AddressRepositoryInterface
{
    public function allByCustomer(int $customerId): Collection
    {
        return Address::where('customer_id', $customerId)->get();
    }

    public function findByIdAndCustomer(int $id, int $customerId): ?Address
    {
        return Address::where('id', $id)->where('customer_id', $customerId)->first();
    }

    public function create(array $data): Address
    {
        return Address::create($data);
    }

    public function update(Address $address, array $data): Address
    {
        $address->update($data);
        return $address->fresh();
    }

    public function delete(Address $address): void
    {
        $address->delete();
    }
}
