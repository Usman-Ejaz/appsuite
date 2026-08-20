<?php

namespace Domains\CMS\Database\Factories;

use Domains\CMS\Models\Form;
use Domains\Identity\Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'company_id' => CompanyFactory::new(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
