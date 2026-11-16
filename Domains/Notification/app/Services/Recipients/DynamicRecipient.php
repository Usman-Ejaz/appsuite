<?php

namespace Domains\Notification\Services\Recipients;

use Domains\Notification\Actions\AbstractTriggerAction;
use Domains\Notification\Models\TriggerActionRecipient;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Illuminate\Support\Collection;

/**
 * Someone tied to the record that fired the trigger, e.g. an order's `customer`. The trigger
 * action for that record says who the code means.
 */
class DynamicRecipient extends AbstractRecipient
{
    public function resolve(TriggerActionRecipient $recipient, AbstractTriggerAction $trigger, NotificationChannel $channel, int $companyId): Collection
    {
        return $recipient->code ? $trigger->dynamicRecipients($recipient->code) : collect();
    }
}
