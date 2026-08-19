<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Permission;
use Illuminate\Database\Eloquent\Model;

class PermissionRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Permission::class;
}
