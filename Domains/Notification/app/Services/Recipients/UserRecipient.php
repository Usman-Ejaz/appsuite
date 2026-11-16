<?php

namespace Domains\Notification\Services\Recipients;

use Domains\Identity\Models\User;
use Domains\Notification\Actions\AbstractTriggerAction;
use Domains\Notification\Models\TriggerActionRecipient;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Illuminate\Support\Collection;

/**
 * One specific user of the company.
 */
class UserRecipient extends AbstractRecipient
{
    public function resolve(TriggerActionRecipient $recipient, AbstractTriggerAction $trigger, NotificationChannel $channel, int $companyId): Collection
    {
        $user = $recipient->recipient;

        return collect($user instanceof User && $user->company_id === $companyId ? [$user] : []);
    }
}
