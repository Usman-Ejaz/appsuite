<?php

namespace Domains\Core\Database\Factories;

use Domains\Core\Enums\AppCode;
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
        // Drawn from AppCode::cases() for a realistic-looking default code (crm, hr, ...)
        // rather than an arbitrary name list. `slug` is generated independently via
        // fake()->unique() rather than derived from $code — deriving it from the same draw
        // caused collisions whenever a caller overrides `code` (e.g. App::factory()->create(
        // ['code' => 'crm'])) without also overriding `slug`: two such calls in one test have
        // real odds of rolling the same underlying $code twice and colliding on the `slug`
        // unique constraint.
        $code = fake()->randomElement(AppCode::cases())->value;
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'name' => $name,
            'label' => $name,
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['Sales', 'Marketing']),
            'code' => $code,
            'color' => fake()->hexColor(),
            'icon' => fake()->word(),
            'is_active' => true,
            'released_at' => fake()->dateTimeBetween('-2 years'),
        ];
    }
}
