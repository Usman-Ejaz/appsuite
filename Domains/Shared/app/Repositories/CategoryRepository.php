<?php

namespace Domains\Shared\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Shared\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository extends BaseRepository
{
    protected string $model = Category::class;

    protected ?string $appCode = null;

    /**
     * Scope every subsequent query/create/update/delete on this repository
     * instance to a single app. Fluent so it can be called inline from the
     * controller, e.g. `$this->categories->forAppCode($appCode)->list(...)`.
     */
    public function forAppCode(string $appCode): static
    {
        $this->appCode = $appCode;

        return $this;
    }

    protected function query(): Builder
    {
        $query = parent::query();

        if ($this->appCode) {
            $query->where('app_code', $this->appCode);
        }

        return $query;
    }

    /**
     * Stamps every category created through this repository with the scoped
     * app — never accepted from the request payload.
     */
    public function create(array $data): Model
    {
        return $this->dbTransaction(function () use ($data) {
            return Category::create([...$data, 'app_code' => $this->appCode]);
        });
    }

    /**
     * Overridden so update()/delete() (which call this internally) stay
     * scoped to the current app_code rather than falling back to the
     * unscoped model lookup the base repository uses.
     */
    public function findOrFail(int|Model $id): Model
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->query()->findOrFail($id);
    }
}
