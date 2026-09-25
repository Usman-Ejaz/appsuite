<?php

namespace Domains\Storage\Models;

use Domains\Core\Models\BaseModel;
use Domains\Storage\Database\Factories\MediaFactory;
use Domains\Storage\Enums\MediaCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'folder_id', 'title', 'starred', 'category', 'disk', 'file_name', 'path', 'mime_type', 'size',
    ];

    /**
     * The attributes that are able to cast.
     */
    protected function casts(): array
    {
        return [
            'category' => MediaCategory::class,
            'starred' => 'boolean',
            'size' => 'integer',
        ];
    }

    /**
     * The model this media item belongs to.
     */
    public function resource(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The folder this media item is filed under, if any.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * The publicly accessible URL for this media item.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    protected static function newFactory(): MediaFactory
    {
        return MediaFactory::new();
    }
}
