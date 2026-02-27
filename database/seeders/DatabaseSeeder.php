<?php

namespace Database\Seeders;

use App\Domain\Address\Models\Address;
use App\Domain\Customer\Models\Customer;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@crm.com',
            'password' => Hash::make('password'),
        ]);

        Customer::factory()
            ->count(5)
            ->create(['user_id' => $user->id])
            ->each(function (Customer $customer) {
                Address::factory()->count(2)->create(['customer_id' => $customer->id]);
            });
    }
}
