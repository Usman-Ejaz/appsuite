<?php

namespace Domains\Shared\Database\Factories;

use Domains\Core\Enums\AppCode;
use Domains\Shared\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'app_code' => AppCode::ECOMMERCE->value,
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    /**
     * Fills in the commerce fields (sku, price, stock) a product needs to
     * actually be sellable, on top of the base display info above.
     */
    public function ecommerce(): static
    {
        return $this->state(fn () => [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'selling_price' => fake()->randomFloat(2, 10, 500),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'status' => 'Active',
        ]);
    }
}
