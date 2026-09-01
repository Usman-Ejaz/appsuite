<?php

namespace Domains\Identity\Models;

use Domains\Core\Traits\BelongsToApp;
use Domains\Core\Traits\HasEditor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use BelongsToApp, HasEditor, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'app_id', 'name', 'label', 'description', 'code', 'guard_name',
    ];
}
