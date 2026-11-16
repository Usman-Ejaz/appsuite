<?php

namespace Domains\Notification\Services\Channels;

use Domains\Notification\Services\Channels\Contracts\NotificationChannel;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

abstract class AbstractChannel implements NotificationChannel
{
    public function isHtml(): bool
    {
        return false;
    }

    public function canReach(object $notifiable): bool
    {
        return true;
    }

    public function route(string $address): ?AnonymousNotifiable
    {
        return $this->driver() ? Notification::route($this->driver(), $address) : null;
    }
}
