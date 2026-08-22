<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Database\Factories\CategoryFactory;

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

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
