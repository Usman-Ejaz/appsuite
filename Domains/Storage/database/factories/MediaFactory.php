<?php

namespace Domains\Storage\Database\Factories;

use Domains\Storage\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $uuid = fake()->unique()->uuid();

        return [
            'folder_id' => null,
            'title' => null,
            'starred' => false,
            'category' => null,
            'disk' => 'public',
            'file_name' => fake()->word().'.jpg',
            'path' => 'media/'.$uuid.'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 5_000_000),
        ];
    }
}
