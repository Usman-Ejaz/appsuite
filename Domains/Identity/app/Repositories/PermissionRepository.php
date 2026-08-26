<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Permission;
use Illuminate\Support\Arr;

class PermissionRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Permission::class;

    protected function query()
    {
        $query = parent::query();

        if ($appId = Arr::get($this->filter, 'app_id')) {
            $query->where('app_id', $appId);
        }

        return $query;
    }
}
