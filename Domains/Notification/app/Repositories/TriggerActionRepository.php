<?php

namespace Domains\Notification\Repositories;

use Domains\Core\Models\Scopes\CompanyScope;
use Domains\Core\Repositories\BaseRepository;
use Domains\Notification\Models\TriggerAction;
use Illuminate\Database\Eloquent\Collection;

class TriggerActionRepository extends BaseRepository
{
    protected string $model = TriggerAction::class;

    /**
     * The active actions a company has on a trigger, with their recipients.
     *
     * Scoped by an explicit company id rather than CompanyScope, which reads the authenticated
     * actor and so filters nothing inside a queue worker.
     *
     * @return Collection<int, TriggerAction>
     */
    public function activeFor(int $companyId, int $triggerId): Collection
    {
        return TriggerAction::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('trigger_id', $triggerId)
            ->where('is_active', true)
            ->with('recipients.recipient')
            ->get();
    }
}
