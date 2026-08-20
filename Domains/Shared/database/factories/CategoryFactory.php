<?php

namespace Domains\Shared\Database\Factories;

use Domains\Shared\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
        ];
    }
}
