<?php

namespace Database\Factories;

use App\Domain\Address\Models\Address;
use App\Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $states = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

        return [
            'customer_id'  => Customer::factory(),
            'cep'          => $this->faker->numerify('########'),
            'street'       => $this->faker->streetName(),
            'number'       => $this->faker->buildingNumber(),
            'complement'   => $this->faker->optional()->secondaryAddress(),
            'neighborhood' => $this->faker->word(),
            'city'         => $this->faker->city(),
            'state'        => $this->faker->randomElement($states),
            'country'      => 'BR',
        ];
    }
}
