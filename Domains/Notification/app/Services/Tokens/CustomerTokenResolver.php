<?php

namespace Domains\Notification\Services\Tokens;

/**
 * `{{ customer.* }}` tokens: every visible customer column.
 */
class CustomerTokenResolver extends AbstractTokenResolver
{
    public function prefix(): string
    {
        return 'customer';
    }
}
