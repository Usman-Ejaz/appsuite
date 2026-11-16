<?php

namespace Domains\Notification\Services\Recipients;

use Domains\Notification\Actions\AbstractTriggerAction;
use Domains\Notification\Models\TriggerActionRecipient;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Illuminate\Support\Collection;

/**
 * A fixed email address or phone number, sent on demand through the action's channel.
 */
class StaticRecipient extends AbstractRecipient
{
    public function resolve(TriggerActionRecipient $recipient, AbstractTriggerAction $trigger, NotificationChannel $channel, int $companyId): Collection
    {
        $notifiable = $recipient->address ? $channel->route($recipient->address) : null;

        return collect($notifiable ? [$notifiable] : []);
    }
}
