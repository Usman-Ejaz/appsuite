<?php

namespace Domains\Ecommerce\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Ecommerce\Models\Brand;

class BrandRepository extends BaseRepository
{
    protected string $model = Brand::class;
}
