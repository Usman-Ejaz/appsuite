<?php

namespace Domains\Shared\Database\Factories;

use Domains\Shared\Enums\PaymentMethodType;
use Domains\Shared\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => PaymentMethodType::CASH,
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
