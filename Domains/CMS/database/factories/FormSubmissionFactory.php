<?php

namespace Domains\CMS\Database\Factories;

use Domains\CMS\Enums\FormSubmissionStatus;
use Domains\CMS\Models\FormSubmission;
use Domains\Identity\Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'form_id' => FormFactory::new(),
            'company_id' => CompanyFactory::new(),
            'data' => ['email' => fake()->safeEmail(), 'message' => fake()->sentence()],
            'status' => FormSubmissionStatus::NEW,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer_url' => fake()->url(),
        ];
    }
}
