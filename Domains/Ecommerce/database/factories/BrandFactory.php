<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
