<?php

namespace Domains\Notification\Services\Channels;

use Domains\Notification\Enums\Channel;

/**
 * A channel that isn't built yet (SMS, push, webhook). Its actions send nothing until it gets its
 * own class here.
 */
class UnsupportedChannel extends AbstractChannel
{
    public function __construct(protected Channel $channel) {}

    public function channel(): Channel
    {
        return $this->channel;
    }

    public function driver(): ?string
    {
        return null;
    }

    public function canReach(object $notifiable): bool
    {
        return false;
    }
}
