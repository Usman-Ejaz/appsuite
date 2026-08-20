<?php

namespace Domains\CMS\Database\Factories;

use Domains\CMS\Enums\BlogStatus;
use Domains\CMS\Models\Blog;
use Domains\Identity\Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'company_id' => CompanyFactory::new(),
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'status' => BlogStatus::DRAFT,
        ];
    }
}
