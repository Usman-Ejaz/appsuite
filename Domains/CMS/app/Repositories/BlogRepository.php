<?php

namespace Domains\CMS\Repositories;

use Domains\CMS\Models\Blog;
use Domains\Core\Repositories\BaseRepository;

class BlogRepository extends BaseRepository
{
    protected string $model = Blog::class;
}
