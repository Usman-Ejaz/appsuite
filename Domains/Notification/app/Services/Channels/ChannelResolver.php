<?php

namespace Domains\Notification\Services\Channels;

use Domains\Notification\Enums\Channel;
use Domains\Notification\Services\Channels\Contracts\NotificationChannel;

class ChannelResolver
{
    /**
     * The only channel-based dispatch. Adding a channel means one new class plus one new arm here.
     */
    public static function make(Channel $channel): NotificationChannel
    {
        return match ($channel) {
            Channel::EMAIL => new EmailChannel,
            Channel::DATABASE => new DatabaseChannel,
            Channel::SMS, Channel::PUSH, Channel::WEBHOOK => new UnsupportedChannel($channel),
        };
    }
}
