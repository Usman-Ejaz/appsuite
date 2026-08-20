<?php

namespace Domains\CMS\Models;

use Domains\CMS\Enums\BlogStatus;
use Domains\Core\Models\BaseModel;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'category_id',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'tags',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BlogStatus::class,
            'published_at' => 'datetime',
            'tags' => 'array',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BlogStatus::PUBLISHED)->where('published_at', '<=', now());
    }
}
