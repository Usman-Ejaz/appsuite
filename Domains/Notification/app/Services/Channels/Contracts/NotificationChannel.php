<?php

namespace Domains\Notification\Services\Channels\Contracts;

use Domains\Notification\Enums\Channel;
use Illuminate\Notifications\AnonymousNotifiable;

/**
 * Everything the engine needs to know about one delivery channel.
 */
interface NotificationChannel
{
    public function channel(): Channel;

    /**
     * The Laravel notification channel it sends through, or null while it isn't built yet.
     */
    public function driver(): ?string;

    /**
     * Whether the body is HTML, so token values must be HTML-escaped.
     */
    public function isHtml(): bool;

    /**
     * Whether this recipient can be reached on the channel.
     */
    public function canReach(object $notifiable): bool;

    /**
     * An on-demand recipient for a fixed address, or null when the channel takes none.
     */
    public function route(string $address): ?AnonymousNotifiable;
}
