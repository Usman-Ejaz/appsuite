<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;

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
}
