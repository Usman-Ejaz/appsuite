<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\ReviewFactory;
use Domains\Ecommerce\Enums\ReviewStatus;
use Domains\Identity\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ecommerce_product_id',
        'customer_id',
        'rating',
        'title',
        'body',
        'status',
    ];

    /**
     * Mirrors the migration's column default on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
        'status' => 'Pending',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'rating' => 'integer',
        ];
    }

    public function ecommerceProduct(): BelongsTo
    {
        return $this->belongsTo(EcommerceProduct::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function newFactory(): ReviewFactory
    {
        return ReviewFactory::new();
    }
}
