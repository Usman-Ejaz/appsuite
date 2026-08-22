<?php

namespace Domains\Identity\Database\Factories;

use Domains\Identity\Enums\CustomerType;
use Domains\Identity\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'type' => CustomerType::INDIVIDUAL,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
}
