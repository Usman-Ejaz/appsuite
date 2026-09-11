<?php

namespace Domains\Shared\Database\Factories;

use Domains\Shared\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Site::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}
