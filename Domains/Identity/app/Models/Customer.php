<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\BaseModel;
use Domains\Identity\Database\Factories\CustomerFactory;
use Domains\Identity\Enums\CustomerType;

class Customer extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'name',
        'business_name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
        'is_active',
    ];

    /**
     * Mirrors the migration's column defaults on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
        'type' => 'Individual',
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
            'type' => CustomerType::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
