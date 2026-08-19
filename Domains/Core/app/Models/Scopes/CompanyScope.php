<?php

namespace Domains\Core\Models\Scopes;

use Domains\Identity\Contracts\Actor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class CompanyScope implements Scope
{

    public function __construct(protected ?Actor $actor)
    {
        // 
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Resolves the current user's company_id at query time rather than at
     * model-boot time — the model class only boots once per PHP process, so
     * a value captured in a constructor would freeze to whichever user
     * happened to trigger that first boot for the rest of the process (a
     * real problem outside the one-process-per-request world: queue
     * workers, Octane, and the test suite all run many "requests" per
     * process).
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! Schema::hasColumn($model->getTable(), 'company_id')) {
            return;
        }

        $companyId = $this->actor?->getCompanyId() ?? null;

        if ($companyId) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }
}
