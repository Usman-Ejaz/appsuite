<?php

namespace Domains\Ecommerce\Models;

use Domains\Core\Models\BaseModel;

class OrderItem extends BaseModel
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
