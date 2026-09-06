<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Role;
use Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UserRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = User::class;

    /**
     * Restrict every subsequent query to users within this company. Null means unrestricted
     * (root managing users across every company).
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
     * Create a new user, optionally assigning roles/app grants in the same request.
     *
     * @param  array  $data  The data to create a new record with — may include `role_ids`/
     *                       `app_codes`, which aren't columns on `users` and are pulled off
     *                       before the plain column data reaches `User::create()`.
     */
    public function create(array $data): Model
    {
        return $this->dbTransaction(function () use ($data) {
            $roleIds = Arr::pull($data, 'role_ids');
            $appCodes = Arr::pull($data, 'app_codes');

            $user = $this->model::create($data);

            $this->syncRoles($user, $roleIds);
            $this->syncApps($user, $appCodes);

            return $user->load(['roles:id,name', 'apps:id,code,name']);
        });
    }

    /**
     * Update an existing user by ID, optionally re-assigning roles/app grants in the same
     * request.
     *
     * @param  mixed  $id  The ID of the record to update.
     * @param  array  $data  The data to update the record with — may include `role_ids`/
     *                       `app_codes`, handled the same way as create() above.
     */
    public function update($id, array $data): Model
    {
        return $this->dbTransaction(function () use ($id, $data) {
            $roleIds = Arr::pull($data, 'role_ids');
            $appCodes = Arr::pull($data, 'app_codes');

            $user = $this->findOrFail($id);
            $user->update($data);

            $this->syncRoles($user, $roleIds);
            $this->syncApps($user, $appCodes);

            return $user->refresh()->load(['roles:id,name', 'apps:id,code,name']);
        });
    }

    /**
     * Replace the user's entire role set. `null` (the field was left out of the request
     * entirely) leaves roles untouched; `[]` clears every role. Ids that don't belong to the
     * user's own company are silently dropped — a user can never be given another company's
     * role, even by a malicious/incorrect id.
     *
     * @param  array<int>|null  $roleIds
     */
    private function syncRoles(User $user, ?array $roleIds): void
    {
        if ($roleIds === null) {
            return;
        }

        $roles = Role::query()
            ->whereIn('id', $roleIds)
            ->where('company_id', $user->company_id)
            ->get();

        $user->syncRoles($roles);
    }

    /**
     * Replace the user's entire individual app-grant set. `null` (the field was left out of the
     * request entirely) leaves grants untouched; `[]` clears every grant. Codes that aren't
     * among the user's own company's subscribed apps are silently dropped.
     *
     * @param  array<string>|null  $appCodes
     */
    private function syncApps(User $user, ?array $appCodes): void
    {
        if ($appCodes === null) {
            return;
        }

        $companyAppIds = $user->company
            ? $user->company->apps()->whereIn('code', $appCodes)->pluck('apps.id')
            : collect();

        $user->apps()->sync($companyAppIds);
    }
}
