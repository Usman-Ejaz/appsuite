<?php

namespace Domains\CMS\Actions;

use Domains\CMS\Enums\BlogStatus;
use Domains\CMS\Models\Blog;

class UnpublishBlog
{
    /**
     * Take the blog off "published" without clearing `published_at`, so a
     * later republish still doesn't reset the original publish date.
     */
    public function handle(Blog $blog): Blog
    {
        $blog->update(['status' => BlogStatus::DRAFT]);

        return $blog->fresh();
    }
}
