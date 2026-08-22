<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\EcommerceProductFactory;
use Domains\Ecommerce\Enums\EcommerceProductStatus;
use Domains\Shared\Models\Category;
use Domains\Shared\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcommerceProduct extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'brand_id',
        'category_id',
        'sku',
        'price',
        'compare_at_price',
        'currency',
        'stock_quantity',
        'track_inventory',
        'status',
        'tags',
    ];

    /**
     * Mirrors the migration's column defaults on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
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
            'status' => EcommerceProductStatus::class,
            'tags' => 'array',
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'track_inventory' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_ecommerce_product')
            ->withPivot('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function newFactory(): EcommerceProductFactory
    {
        return EcommerceProductFactory::new();
    }
}
