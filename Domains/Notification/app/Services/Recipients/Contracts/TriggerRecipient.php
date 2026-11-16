<?php

namespace Domains\Notification\Services\Recipients\Contracts;

use Domains\Notification\Actions\AbstractTriggerAction;
use Domains\Notification\Models\TriggerActionRecipient;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Illuminate\Support\Collection;

/**
 * Turns one trigger action recipient row into the people to notify.
 */
interface TriggerRecipient
{
    /**
     * @return Collection<int, object> Notifiable models or on-demand notifiables.
     */
    public function resolve(TriggerActionRecipient $recipient, AbstractTriggerAction $trigger, NotificationChannel $channel, int $companyId): Collection;
}
