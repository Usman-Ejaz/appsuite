<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\OrderFactory;
use Domains\Ecommerce\Enums\OrderStatus;
use Domains\Identity\Models\Customer;
use Domains\Shared\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'payment_method_id',
        'coupon_id',
        'order_number',
        'status',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'total',
        'currency',
        'notes',
        'cancelled_at',
    ];

    /**
     * Mirrors the migration's column defaults on the in-memory model
     * immediately after creation — Eloquent doesn't otherwise reflect a
     * DB-level `->default(...)` until the model is re-fetched.
     */
    protected $attributes = [
        'status' => 'Pending',
        'subtotal' => 0,
        'discount_total' => 0,
        'tax_total' => 0,
        'shipping_total' => 0,
        'total' => 0,
        'currency' => 'USD',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'total' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
