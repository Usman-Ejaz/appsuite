<?php

namespace Domains\Notification\Services\Tokens;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;

/**
 * `{{ recipient.* }}` tokens: who the notification is going to. A stored user or customer
 * exposes its identifying fields; a bare address exposes itself as `email` or `phone`.
 */
class RecipientTokenResolver extends AbstractTokenResolver
{
    /**
     * The only attributes exposed as tokens.
     *
     * @var list<string>
     */
    protected array $only = ['name', 'email', 'phone'];

    public function __construct(protected object $notifiable)
    {
        parent::__construct($notifiable instanceof Model ? $notifiable : null);
    }

    public function prefix(): string
    {
        return 'recipient';
    }

    public function get(string $path): mixed
    {
        if (! $this->notifiable instanceof AnonymousNotifiable) {
            return parent::get($path);
        }

        $address = collect($this->notifiable->routes)->flatten()->first();

        return match ($path) {
            'email' => str_contains((string) $address, '@') ? $address : null,
            'phone' => str_contains((string) $address, '@') ? null : $address,
            default => null,
        };
    }
}
