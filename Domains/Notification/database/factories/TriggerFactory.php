<?php

namespace Domains\Notification\Database\Factories;

use Domains\Notification\Models\Trigger;
use Illuminate\Database\Eloquent\Factories\Factory;

class TriggerFactory extends Factory
{
    protected $model = Trigger::class;

    public function definition(): array
    {
        $module = ucfirst(fake()->unique()->word());

        return [
            'code' => "Test:{$module}:Created",
            'name' => "{$module} Created",
            'description' => fake()->sentence(),
            'domain' => 'Test',
            'module' => $module,
            'app_code' => 'test',
        ];
    }
}
