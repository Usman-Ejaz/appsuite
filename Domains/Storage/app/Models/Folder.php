<?php

namespace Domains\Storage\Models;

use Domains\Core\Models\BaseModel;
use Domains\Storage\Database\Factories\FolderFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_id', 'name',
    ];

    /**
     * The parent folder, if any.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The direct child folders.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The media items filed directly under this folder.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    protected static function newFactory(): FolderFactory
    {
        return FolderFactory::new();
    }
}
