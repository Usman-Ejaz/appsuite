<?php

namespace Domains\Notification\Services\Channels;

use Domains\Notification\Enums\Channel;
use Illuminate\Notifications\AnonymousNotifiable;

class EmailChannel extends AbstractChannel
{
    public function channel(): Channel
    {
        return Channel::EMAIL;
    }

    public function driver(): ?string
    {
        return 'mail';
    }

    public function isHtml(): bool
    {
        return true;
    }

    /**
     * Only a valid email address can be mailed.
     */
    public function canReach(object $notifiable): bool
    {
        $address = $notifiable instanceof AnonymousNotifiable
            ? $notifiable->routes['mail'] ?? null
            : (method_exists($notifiable, 'routeNotificationFor') ? $notifiable->routeNotificationFor('mail') : null);

        return is_string($address) && filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
    }
}
