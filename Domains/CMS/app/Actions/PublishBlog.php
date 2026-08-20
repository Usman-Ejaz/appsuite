<?php

namespace Domains\CMS\Actions;

use Domains\CMS\Enums\BlogStatus;
use Domains\CMS\Models\Blog;

class PublishBlog
{
    /**
     * Publish the blog. `published_at` is set only if it isn't already —
     * republishing never resets the original publish date.
     */
    public function handle(Blog $blog): Blog
    {
        $blog->update([
            'status' => BlogStatus::PUBLISHED,
            'published_at' => $blog->published_at ?? now(),
        ]);

        return $blog->fresh();
    }
}
