<?php

use Domains\Notification\Enums\Channel;
use Domains\Notification\Services\Channels\ChannelResolver;
use Domains\Notification\Services\Channels\DatabaseChannel;
use Domains\Notification\Services\Channels\EmailChannel;
use Domains\Notification\Services\Channels\UnsupportedChannel;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class);

test('every channel resolves to its class', function (Channel $channel, string $class, ?string $driver) {
    $resolved = ChannelResolver::make($channel);

    expect($resolved)->toBeInstanceOf($class)
        ->and($resolved->channel())->toBe($channel)
        ->and($resolved->driver())->toBe($driver);
})->with([
    'email' => [Channel::EMAIL, EmailChannel::class, 'mail'],
    'database' => [Channel::DATABASE, DatabaseChannel::class, 'database'],
    'sms is not built yet' => [Channel::SMS, UnsupportedChannel::class, null],
    'push is not built yet' => [Channel::PUSH, UnsupportedChannel::class, null],
    'webhook is not built yet' => [Channel::WEBHOOK, UnsupportedChannel::class, null],
]);

test('email only reaches valid addresses and escapes html', function () {
    $email = ChannelResolver::make(Channel::EMAIL);

    expect($email->isHtml())->toBeTrue()
        ->and($email->canReach(Notification::route('mail', 'ops@example.com')))->toBeTrue()
        ->and($email->canReach(Notification::route('mail', '+15550100')))->toBeFalse();
});

test('the in-app inbox has no route for a bare address', function () {
    expect(ChannelResolver::make(Channel::DATABASE)->route('ops@example.com'))->toBeNull()
        ->and(ChannelResolver::make(Channel::DATABASE)->canReach(Notification::route('mail', 'ops@example.com')))->toBeFalse();
});
