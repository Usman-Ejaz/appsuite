<?php

namespace Domains\CMS\Repositories;

use Domains\CMS\Enums\BlogStatus;
use Domains\CMS\Models\Blog;
use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\ApiKey;
use Illuminate\Support\Facades\Auth;

class BlogRepository extends BaseRepository
{
    protected string $model = Blog::class;

    protected function query()
    {
        $query = parent::query()->with(['author', 'category']);

        $actor = Auth::user();

        // ApiKey actors (public-website integrations) can never see draft/
        // unpublished blogs, regardless of their granted abilities — a
        // hard-coded restriction, not a permission string.
        if ($actor instanceof ApiKey || ! $actor?->can('cms:blogs:create')) {
            $query->where('status', BlogStatus::PUBLISHED)->where('published_at', '<=', now());
        }

        return $query;
    }
}
