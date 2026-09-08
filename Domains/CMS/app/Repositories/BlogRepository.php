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
}
