<?php

namespace Domains\Core\Database\Factories;

use Domains\Core\Models\App;
use Domains\Core\Models\Integration;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Integration::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'app_id' => App::factory(),
            'name' => fake()->unique()->company(),
            'provider' => fake()->randomElement(['stripe', 'slack', 'twilio', 'google', 'zoom']),
            'config' => [],
            'is_active' => true,
        ];
    }
}
