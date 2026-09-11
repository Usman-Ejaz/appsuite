<?php

namespace Domains\Shared\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Shared\Models\Site;

class SiteRepository extends BaseRepository
{
    protected string $model = Site::class;
}
