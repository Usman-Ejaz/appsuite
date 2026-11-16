<?php

namespace Domains\Notification\Services\Channels;

use Domains\Notification\Enums\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;

/**
 * The in-app inbox.
 */
class DatabaseChannel extends AbstractChannel
{
    public function channel(): Channel
    {
        return Channel::DATABASE;
    }

    public function driver(): ?string
    {
        return 'database';
    }

    /**
     * Only a stored model has an inbox.
     */
    public function canReach(object $notifiable): bool
    {
        return $notifiable instanceof Model && method_exists($notifiable, 'notifications');
    }

    /**
     * A bare address has no inbox.
     */
    public function route(string $address): ?AnonymousNotifiable
    {
        return null;
    }
}
