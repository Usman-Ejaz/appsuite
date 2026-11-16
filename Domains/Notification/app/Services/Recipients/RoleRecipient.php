<?php

namespace Domains\Notification\Services\Recipients;

use Domains\Identity\Models\Role;
use Domains\Notification\Actions\AbstractTriggerAction;
use Domains\Notification\Models\TriggerActionRecipient;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Illuminate\Support\Collection;

/**
 * Every user of the company holding the role.
 */
class RoleRecipient extends AbstractRecipient
{
    public function resolve(TriggerActionRecipient $recipient, AbstractTriggerAction $trigger, NotificationChannel $channel, int $companyId): Collection
    {
        $role = $recipient->recipient;

        if (! $role instanceof Role || $role->company_id !== $companyId) {
            return collect();
        }

        return $this->companyUsers($companyId)->role($role)->get();
    }
}
