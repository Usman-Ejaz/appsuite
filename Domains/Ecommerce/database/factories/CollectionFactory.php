<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
