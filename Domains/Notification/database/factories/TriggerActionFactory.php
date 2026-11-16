<?php

namespace Domains\Notification\Database\Factories;

use Domains\Identity\Database\Factories\CompanyFactory;
use Domains\Notification\Enums\Channel;
use Domains\Notification\Models\TriggerAction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TriggerActionFactory extends Factory
{
    protected $model = TriggerAction::class;

    public function definition(): array
    {
        return [
            'company_id' => CompanyFactory::new(),
            'trigger_id' => TriggerFactory::new(),
            'name' => fake()->words(3, true),
            'channel' => Channel::EMAIL,
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'conditions' => null,
            'is_active' => true,
        ];
    }

    /**
     * An in-app (database) action.
     */
    public function database(): static
    {
        return $this->state(['channel' => Channel::DATABASE]);
    }
}
