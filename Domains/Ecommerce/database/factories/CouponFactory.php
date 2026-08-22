<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Enums\CouponType;
use Domains\Ecommerce\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE##??')),
            'type' => CouponType::FIXED,
            'value' => fake()->randomFloat(2, 5, 50),
            'is_active' => true,
        ];
    }
}
