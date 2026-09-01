<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;
use Domains\Core\Traits\HasParentChild;
use Domains\Shared\Database\Factories\CategoryFactory;
use Domains\Shared\Enums\CategoryStatus;

class Category extends BaseModel
{
    use HasParentChild;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'app_code',
        'name',
        'slug',
        'description',
        'is_featured',
        'status',
        'parent_id',
    ];

    /**
     * Mirrors the migration's NOT NULL columns' effective defaults on the
     * in-memory model immediately after creation.
     */
    protected $attributes = [
        'is_featured' => false,
        'status' => CategoryStatus::DRAFT->value,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * `status` is validated against CategoryStatus at the request layer but
     * stored as a plain string, mirroring how Product::status is handled.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
