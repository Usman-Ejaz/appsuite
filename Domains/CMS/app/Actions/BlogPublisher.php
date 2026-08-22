<?php

namespace Domains\CMS\Actions;

use Domains\CMS\Enums\BlogStatus;
use Domains\CMS\Models\Blog;

class BlogPublisher
{
    public static function publish(Blog $blog): Blog
    {
        $blog->update([
            'status' => BlogStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        return $blog->fresh();
    }

    public static function unpublish(Blog $blog): Blog
    {
        $blog->update([
            'status' => BlogStatus::DRAFT,
            'unpublished_at' => now(),
        ]);

        return $blog->fresh();
    }
}
