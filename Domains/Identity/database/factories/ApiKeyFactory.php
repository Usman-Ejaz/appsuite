<?php

namespace Domains\Identity\Database\Factories;

use Domains\Identity\Models\ApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApiKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ApiKey::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $credentials = ApiKey::generateCredentials();

        return [
            'name' => fake()->words(2, true),
            'api_key' => $credentials['key'],
            'api_secret' => hash('sha256', $credentials['secret']),
            'abilities' => null,
            'expires_at' => null,
        ];
    }
}
