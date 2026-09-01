<?php

namespace Domains\Shared\Database\Factories;

use Domains\Core\Enums\AppCode;
use Domains\Shared\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'app_code' => AppCode::ECOMMERCE->value,
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
        ];
    }
}
