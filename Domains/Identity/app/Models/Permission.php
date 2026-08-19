<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\App;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'app_id', 'name', 'label', 'description', 'code', 'guard_name',
    ];

    /**
     * The app this permission belongs to.
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
