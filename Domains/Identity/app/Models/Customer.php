<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\BaseModel;

class Customer extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // 
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 
        ];
    }
}
