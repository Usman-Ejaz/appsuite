<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;

class Category extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];
}
