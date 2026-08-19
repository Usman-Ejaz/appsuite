<?php

namespace Domains\Identity\Database\Factories;

use Domains\Identity\Enums\CompanyStatus;
use Domains\Identity\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->catchPhrase(),
            'status' => CompanyStatus::ACTIVE,
            'license_number' => fake()->unique()->numerify('LIC-########'),
        ];
    }
}
