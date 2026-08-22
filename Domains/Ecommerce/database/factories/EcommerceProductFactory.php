<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Enums\EcommerceProductStatus;
use Domains\Ecommerce\Models\EcommerceProduct;
use Domains\Shared\Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EcommerceProductFactory extends Factory
{
    protected $model = EcommerceProduct::class;

    public function definition(): array
    {
        return [
            'product_id' => ProductFactory::new(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'price' => fake()->randomFloat(2, 10, 500),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'status' => EcommerceProductStatus::ACTIVE,
        ];
    }
}
