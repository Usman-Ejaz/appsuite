<?php

namespace Domains\Shared\Database\Factories;

use Domains\Shared\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
