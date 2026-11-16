<?php

namespace Domains\Notification\Database\Factories;

use Domains\Notification\Enums\RecipientType;
use Domains\Notification\Models\TriggerActionRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

class TriggerActionRecipientFactory extends Factory
{
    protected $model = TriggerActionRecipient::class;

    public function definition(): array
    {
        return [
            'trigger_action_id' => TriggerActionFactory::new(),
            'type' => RecipientType::STATIC,
            'address' => fake()->safeEmail(),
        ];
    }

    /**
     * The company owner.
     */
    public function owner(): static
    {
        return $this->state(['type' => RecipientType::OWNER, 'address' => null]);
    }

    /**
     * A dynamic recipient of the record that fired the trigger, e.g. `customer`.
     */
    public function dynamic(string $code): static
    {
        return $this->state(['type' => RecipientType::DYNAMIC, 'code' => $code, 'address' => null]);
    }
}
