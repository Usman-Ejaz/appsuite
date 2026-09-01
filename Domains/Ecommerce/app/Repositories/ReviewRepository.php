<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ReviewRepository extends BaseRepository
{
    protected string $model = Review::class;
}
