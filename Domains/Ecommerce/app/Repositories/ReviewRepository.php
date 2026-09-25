<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Review;

class ReviewRepository extends BaseRepository
{
    protected string $model = Review::class;
}
