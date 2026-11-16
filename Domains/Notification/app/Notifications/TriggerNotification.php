<?php

namespace Domains\Notification\Notifications;

use Domains\Notification\Enums\NotificationStatus;
use Domains\Notification\Models\NotificationLog;
use Domains\Notification\Models\TriggerAction;
use Domains\Notification\Services\Channels\ChannelResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * The one notification every trigger action sends, whatever the trigger. Its subject and body
 * are already rendered for the recipient; it only delivers them through the action's channel.
 */
class TriggerNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the notification may be attempted.
     */
    public int $tries = 3;

    public function __construct(
        public TriggerAction $action,
        public Model $resource,
        public string $subject,
        public string $body,
    ) {
        $this->onQueue(config('notification.queue'));
    }

    /**
     * Seconds to wait before each retry.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60];
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return array_filter([ChannelResolver::make($this->action->channel)->driver()]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $body = $this->body !== strip_tags($this->body) ? $this->body : nl2br($this->body);

        return (new MailMessage)
            ->subject($this->subject)
            ->view('notification::mail.trigger', ['subject' => $this->subject, 'body' => $body]);
    }

    /**
     * @return array{title: string, body: string, trigger_action_id: int, resource_type: string, resource_id: mixed}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->subject,
            'body' => $this->body,
            'trigger_action_id' => $this->action->id,
            'resource_type' => $this->resource->getMorphClass(),
            'resource_id' => $this->resource->getKey(),
        ];
    }

    /**
     * Every attempt failed.
     */
    public function failed(Throwable $exception): void
    {
        NotificationLog::create([
            'company_id' => $this->action->company_id,
            'trigger_id' => $this->action->trigger_id,
            'trigger_action_id' => $this->action->id,
            'channel' => $this->action->channel,
            'resource_type' => $this->resource->getMorphClass(),
            'resource_id' => $this->resource->getKey(),
            'data' => ['subject' => $this->subject, 'body' => $this->body],
            'status' => NotificationStatus::FAILED,
            'error' => $exception->getMessage(),
        ]);
    }
}
