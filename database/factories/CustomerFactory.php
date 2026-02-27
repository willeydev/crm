<?php

namespace Database\Factories;

use App\Domain\Customer\Models\Customer;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'name'     => $this->faker->name(),
            'email'    => $this->faker->safeEmail(),
            'phone'    => $this->faker->numerify('(##) #####-####'),
            'document' => $this->faker->numerify('###.###.###-##'),
        ];
    }
}
