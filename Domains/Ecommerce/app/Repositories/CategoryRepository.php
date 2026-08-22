<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Shared\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected string $model = Category::class;
}
