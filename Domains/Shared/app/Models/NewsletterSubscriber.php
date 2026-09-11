<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Traits\HasSites;

class NewsletterSubscriber extends BaseModel
{
    use HasSites;

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
