<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;
use Domains\Ecommerce\Database\Factories\CampaignFactory;
use Domains\Ecommerce\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'starts_at',
        'ends_at',
    ];

    /**
     * Mirrors the migration's column default on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
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
            'status' => CampaignStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    protected static function newFactory(): CampaignFactory
    {
        return CampaignFactory::new();
    }
}
