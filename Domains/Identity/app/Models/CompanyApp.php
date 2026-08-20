<?php

namespace Domains\Identity\Models;

use Domains\Core\Traits\BelongsToApp;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyApp extends Pivot
{
    use BelongsToApp;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id', 'app_id', 'assigned_by', 'assigned_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }
}
