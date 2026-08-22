<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Database\Factories\ProductFactory;

class Product extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'is_active',
    ];

    /**
     * Mirrors the migration's column default on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
