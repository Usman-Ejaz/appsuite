<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\ApiKey;

class ApiKeyRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = ApiKey::class;
}
