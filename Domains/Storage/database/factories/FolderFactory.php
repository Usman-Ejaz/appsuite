<?php

namespace Domains\Storage\Database\Factories;

use Domains\Storage\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

class FolderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Folder::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
        ];
    }
}
