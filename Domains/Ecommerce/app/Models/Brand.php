<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
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

    public function ecommerceProducts(): HasMany
    {
        return $this->hasMany(EcommerceProduct::class);
    }

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }
}
