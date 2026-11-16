<?php

namespace Domains\Notification\Services\Recipients;

use Domains\Core\Models\Scopes\CompanyScope;
use Domains\Identity\Models\User;
use Domains\Notification\Services\Recipients\Contracts\TriggerRecipient;
use Illuminate\Database\Eloquent\Builder;

/**
 * Recipients resolve inside a queue worker, where CompanyScope filters nothing, so every user
 * lookup is pinned to the company explicitly.
 */
abstract class AbstractRecipient implements TriggerRecipient
{
    /**
     * @return Builder<User>
     */
    protected function companyUsers(int $companyId): Builder
    {
        return User::withoutGlobalScope(CompanyScope::class)->where('company_id', $companyId);
    }
}
