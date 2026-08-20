<?php

namespace Domains\CMS\Database\Factories;

use Domains\CMS\Enums\FormFieldType;
use Domains\CMS\Models\FormField;
use Domains\Identity\Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'form_id' => FormFactory::new(),
            'company_id' => CompanyFactory::new(),
            'label' => fake()->words(3, true),
            'name' => fake()->unique()->word(),
            'type' => FormFieldType::TEXT,
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
