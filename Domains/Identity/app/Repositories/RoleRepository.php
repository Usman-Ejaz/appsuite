<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Role;
use Illuminate\Database\Eloquent\Model;

class RoleRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Role::class;

    /**
     * Create a new role.
     *
     * Spatie's `Role::create()` static method rejects any name/guard pair that already
     * exists anywhere, regardless of `company_id`, which would block two companies from
     * having a same-named role (e.g. "Owner"). Creating through the query builder bypasses
     * that global check and relies on our own `[company_id, name, guard_name]` unique
     * index instead.
     *
     * @param  array  $data  The data to create a new record with.
     */
    public function create(array $data): Model
    {
        return $this->dbTransaction(function () use ($data) {
            return $this->model::query()->create($data);
        });
    }
}
