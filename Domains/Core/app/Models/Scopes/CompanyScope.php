<?php

namespace Domains\Core\Models\Scopes;

use Domains\Identity\Contracts\Actor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Resolves the current actor via Auth::user() at query time rather than
     * a constructor-injected reference — the model class only boots once
     * per PHP process, so an actor captured in the constructor would freeze
     * to whichever one happened to trigger that first boot for the rest of
     * the process (a real problem outside the one-process-per-request
     * world: queue workers, Octane, and the test suite all run many
     * "requests" per process). This previously took the actor via the
     * constructor from `request()->user()`, which only resolves once an
     * HTTP request has actually gone through auth middleware — resolving
     * `Auth::user()` fresh on every call fixes both the staleness and the
     * non-HTTP-context (tests, tinker, queue jobs) cases.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! Schema::hasColumn($model->getTable(), 'company_id')) {
            return;
        }

        $actor = Auth::user();
        $companyId = $actor instanceof Actor ? $actor->getCompanyId() : null;

        if ($companyId) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }
}
