<?php

namespace Domains\Notification\Database\Factories;

use Domains\Identity\Database\Factories\CompanyFactory;
use Domains\Notification\Enums\Channel;
use Domains\Notification\Enums\NotificationStatus;
use Domains\Notification\Models\NotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationLogFactory extends Factory
{
    protected $model = NotificationLog::class;

    public function definition(): array
    {
        return [
            'company_id' => CompanyFactory::new(),
            'channel' => Channel::EMAIL,
            'address' => fake()->safeEmail(),
            'data' => ['subject' => fake()->sentence(4), 'body' => fake()->sentence()],
            'status' => NotificationStatus::SENT,
        ];
    }
}
