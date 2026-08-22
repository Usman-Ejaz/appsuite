<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Ecommerce\Models\Order;
use Domains\Identity\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => CustomerFactory::new(),
            'status' => OrderStatus::PENDING,
            'currency' => 'USD',
        ];
    }
}
