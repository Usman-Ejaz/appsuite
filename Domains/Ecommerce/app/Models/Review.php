<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\ReviewFactory;
use Domains\Ecommerce\Enums\ReviewStatus;
use Domains\Identity\Models\Customer;
use Domains\Shared\Traits\BelongsToSite;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends BaseModel
{
    use BelongsToSite;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
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

    /**
     * The record the review was left on. Only products are reviewable today.
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
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
