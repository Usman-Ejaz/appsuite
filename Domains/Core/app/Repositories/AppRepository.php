<?php

namespace Domains\Core\Repositories;

use Domains\Core\Models\App;

class AppRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = App::class;
}
