<?php

namespace Domains\Core\Database\Factories;

use Domains\Core\Models\App;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = App::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['CRM', 'HR', 'Finance', 'Inventory', 'Support']);

        return [
            'name' => $name,
            'label' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['Sales', 'Marketing']),
            'code' => str($name)->slug('_'),
            'color' => fake()->hexColor(),
            'icon' => fake()->word(),
            'is_active' => true,
            'released_at' => fake()->dateTimeBetween('-2 years'),
        ];
    }
}
