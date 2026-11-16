<?php

namespace Domains\Notification\Services\Recipients;

use Domains\Notification\Actions\AbstractTriggerAction;
use Domains\Notification\Models\TriggerActionRecipient;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Illuminate\Support\Collection;

/**
 * The company's owner.
 */
class OwnerRecipient extends AbstractRecipient
{
    public function resolve(TriggerActionRecipient $recipient, AbstractTriggerAction $trigger, NotificationChannel $channel, int $companyId): Collection
    {
        return $this->companyUsers($companyId)->where('is_owner', true)->get();
    }
}
