<?php

namespace Domains\Notification\Services\Tokens;

/**
 * `{{ form.* }}` tokens: every visible form column.
 */
class FormTokenResolver extends AbstractTokenResolver
{
    public function prefix(): string
    {
        return 'form';
    }
}
