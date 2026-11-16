<?php

namespace Domains\Notification\Listeners;

use Domains\Notification\Enums\NotificationStatus;
use Domains\Notification\Models\NotificationLog;
use Domains\Notification\Notifications\TriggerNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationSent;

/**
 * Logs every trigger notification once its channel has sent it.
 */
class LogNotificationSent
{
    public function handle(NotificationSent $event): void
    {
        $notification = $event->notification;

        if (! $notification instanceof TriggerNotification) {
            return;
        }

        $notifiable = $event->notifiable;

        NotificationLog::create([
            'company_id' => $notification->action->company_id,
            'trigger_id' => $notification->action->trigger_id,
            'trigger_action_id' => $notification->action->id,
            'channel' => $notification->action->channel,
            'notifiable_type' => $notifiable instanceof Model ? $notifiable->getMorphClass() : null,
            'notifiable_id' => $notifiable instanceof Model ? $notifiable->getKey() : null,
            'address' => $this->address($notifiable, $event->channel),
            'resource_type' => $notification->resource->getMorphClass(),
            'resource_id' => $notification->resource->getKey(),
            'data' => ['subject' => $notification->subject, 'body' => $notification->body],
            'status' => NotificationStatus::SENT,
        ]);
    }

    protected function address(object $notifiable, string $channel): ?string
    {
        $route = $notifiable instanceof AnonymousNotifiable
            ? ($notifiable->routes[$channel] ?? null)
            : ($channel !== 'database' && method_exists($notifiable, 'routeNotificationFor') ? $notifiable->routeNotificationFor($channel) : null);

        return is_array($route) ? (string) array_key_first($route) : $route;
    }
}
