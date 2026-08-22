<?php

namespace Domains\Ecommerce\Database\Factories;

use Domains\Ecommerce\Enums\CampaignStatus;
use Domains\Ecommerce\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
            'status' => CampaignStatus::DRAFT,
        ];
    }
}
