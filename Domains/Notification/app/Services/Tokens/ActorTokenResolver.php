<?php

namespace Domains\Notification\Services\Tokens;

/**
 * `{{ actor.* }}` tokens: whoever made the change, a user or an API key. Limited to identifying
 * fields, since an API key's own columns include its key.
 */
class ActorTokenResolver extends AbstractTokenResolver
{
    /**
     * The only attributes exposed as tokens.
     *
     * @var list<string>
     */
    protected array $only = ['name', 'email'];

    public function prefix(): string
    {
        return 'actor';
    }
}
