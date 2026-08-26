<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class RoleRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Role::class;

    /**
     * Restrict every subsequent query to roles within this company. Null means unrestricted
     * (root managing roles across every company).
     */
    protected ?int $companyId = null;

    /**
     * Scope every subsequent query to the given company. Pass null to remove any restriction.
     *
     * @param  int|null  $companyId  The company to restrict to, or null for no restriction.
     */
    public function scopeToCompany(?int $companyId): static
    {
        $this->companyId = $companyId;

        return $this;
    }

    protected function query()
    {
        $query = parent::query();

        if ($this->companyId !== null) {
            $query->where('company_id', $this->companyId);
        }

        return $query;
    }

    /**
     * Find a record by ID or return the Model instance if provided. Routed through the scoped
     * query so an owner's company restriction is enforced here too, since update() and delete()
     * on the base repository resolve records through this method rather than query().
     *
     * @param  int|Model  $id  The ID of the record or a Model instance.
     * @return Model
     */
    public function findOrFail(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->query()->findOrFail($id);
    }

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
            $permissionIds = Arr::pull($data, 'permissions', []);
            $role = $this->model::query()->create($data);
            if ($permissionIds) {
                $role->syncPermissions(Permission::whereIn('id', $permissionIds)->get());
            }

            return $role->load('permissions');
        });
    }

    /**
     * Update an existing role by ID.
     *
     * @param  mixed  $id  The ID of the record to update.
     * @param  array  $data  The data to update the record with.
     */
    public function update($id, array $data): Model
    {
        return $this->dbTransaction(function () use ($id, $data) {
            $permissionIds = Arr::pull($data, 'permissions');
            $role = $this->findOrFail($id);
            $role->update($data);
            if ($permissionIds !== null) {
                $role->syncPermissions(Permission::whereIn('id', $permissionIds)->get());
            }

            return $role->load('permissions');
        });
    }
}
