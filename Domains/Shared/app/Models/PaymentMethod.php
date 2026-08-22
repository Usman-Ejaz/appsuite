<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Database\Factories\PaymentMethodFactory;
use Domains\Shared\Enums\PaymentMethodType;

class PaymentMethod extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'provider',
        'instructions',
        'is_default',
        'is_active',
    ];

    /**
     * Mirrors the migration's column defaults on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
        'is_default' => false,
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
            'type' => PaymentMethodType::class,
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): PaymentMethodFactory
    {
        return PaymentMethodFactory::new();
    }
}
