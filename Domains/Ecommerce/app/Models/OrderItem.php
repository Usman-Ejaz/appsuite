<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'ecommerce_product_id',
        'product_name',
        'product_sku',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function ecommerceProduct(): BelongsTo
    {
        return $this->belongsTo(EcommerceProduct::class);
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }
}
