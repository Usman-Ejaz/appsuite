<?php

namespace Domains\CMS\Database\Factories;

use Domains\CMS\Models\FormAction;
use Domains\Identity\Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormActionFactory extends Factory
{
    protected $model = FormAction::class;

    public function definition(): array
    {
        return [
            'form_id' => FormFactory::new(),
            'company_id' => CompanyFactory::new(),
            'type' => fake()->randomElement(['send_email', 'call_webhook']),
            'name' => fake()->words(2, true),
            'config' => [],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
