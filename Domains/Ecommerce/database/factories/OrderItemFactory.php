<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 200);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'order_id' => OrderFactory::new(),
            'product_name' => fake()->words(3, true),
            'product_sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'unit_price' => $price,
            'quantity' => $quantity,
            'subtotal' => bcmul((string) $price, (string) $quantity, 2),
        ];
    }
}
