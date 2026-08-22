<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Collection;

class CollectionRepository extends BaseRepository
{
    protected string $model = Collection::class;
}
