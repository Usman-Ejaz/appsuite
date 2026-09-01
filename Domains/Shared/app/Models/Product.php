<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'app_code',
        'name',
        'slug',
        'description',
        'thumbnail',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'canonical_url',
        'brand_id',
        'category_id',
        'sku',
        'cost_price',
        'selling_price',
        'compare_at_price',
        'currency',
        'stock_quantity',
        'reorder_threshold',
        'track_inventory',
        'status',
        'tags',
    ];

    /**
     * Mirrors the migration's column defaults on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
        'is_active' => true,
        'is_featured' => false,
        'currency' => 'USD',
        'stock_quantity' => 0,
        'track_inventory' => true,
        'status' => 'Draft',
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
            'is_featured' => 'boolean',
            'tags' => 'array',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'track_inventory' => 'boolean',
        ];
    }

    /**
     * Category is a Shared model, safe to reference directly. The Ecommerce-specific
     * `brand`, `collections`, `reviews`, and `orderItems` relations are registered onto
     * this model from Domains\Ecommerce\Providers\EcommerceServiceProvider via
     * resolveRelationUsing(), since Domains/Shared must not import Domains/Ecommerce
     * classes. `status` is validated against ProductStatus at the request
     * layer but stored as a plain string for the same reason.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
