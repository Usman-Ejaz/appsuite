<?php

namespace Domains\Notification\Services\Tokens;

/**
 * `{{ trigger.* }}` tokens: the trigger that fired, e.g. `{{ trigger.name }}`.
 */
class TriggerTokenResolver extends AbstractTokenResolver
{
    public function prefix(): string
    {
        return 'trigger';
    }
}
