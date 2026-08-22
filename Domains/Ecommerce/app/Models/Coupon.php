<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\CouponFactory;
use Domains\Ecommerce\Enums\CouponType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'campaign_id',
        'code',
        'type',
        'value',
        'max_discount_amount',
        'min_order_amount',
        'usage_limit',
        'usage_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    /**
     * Mirrors the migration's column defaults on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
        'usage_count' => 0,
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
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected static function newFactory(): CouponFactory
    {
        return CouponFactory::new();
    }
}
