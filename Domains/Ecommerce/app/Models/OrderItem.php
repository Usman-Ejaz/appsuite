<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\OrderItemFactory;
use Domains\Shared\Models\Product;
use Domains\Shared\Traits\HasSites;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends BaseModel
{
    use HasSites;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'product_id',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }
}
