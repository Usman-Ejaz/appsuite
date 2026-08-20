<?php

namespace Domains\Identity\Models;

use Domains\Core\Traits\BelongsToApp;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserApp extends Pivot
{
    use BelongsToApp;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id', 'app_id', 'role_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
