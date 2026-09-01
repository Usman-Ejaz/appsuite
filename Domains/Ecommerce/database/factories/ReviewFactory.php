<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Enums\ReviewStatus;
use Domains\Ecommerce\Models\Review;
use Domains\Shared\Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'product_id' => ProductFactory::new()->ecommerce(),
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => ReviewStatus::PENDING,
        ];
    }
}
