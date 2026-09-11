<?php

namespace Domains\Shared\Models;

use Domains\Core\Models\BaseModel;
use Domains\Shared\Database\Factories\SiteFactory;
use Domains\Shared\Enums\SiteStatus;

class Site extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'title',
        'tag_line',
        'url',
        'status',
        'setting',
        'allow_comments_on_blogs',
        'requires_comment_approval',
        'notify_on_comments',
        'spam_filtering',
        'blocklist_keywords',
    ];

    /**
     * Mirrors the migration's column defaults on the in-memory model
     * immediately after creation.
     */
    protected $attributes = [
        'status' => SiteStatus::DRAFT->value,
        'allow_comments_on_blogs' => true,
        'requires_comment_approval' => true,
        'notify_on_comments' => true,
        'spam_filtering' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * `status` is validated against SiteStatus at the request layer but
     * stored as a plain string, mirroring how Product::status is handled.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'setting' => 'array',
            'allow_comments_on_blogs' => 'boolean',
            'requires_comment_approval' => 'boolean',
            'notify_on_comments' => 'boolean',
            'spam_filtering' => 'boolean',
        ];
    }

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }
}
