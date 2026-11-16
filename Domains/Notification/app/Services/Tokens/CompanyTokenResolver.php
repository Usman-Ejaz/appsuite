<?php

namespace Domains\Notification\Services\Tokens;

/**
 * `{{ company.* }}` tokens: the company the trigger fired for.
 */
class CompanyTokenResolver extends AbstractTokenResolver
{
    public function prefix(): string
    {
        return 'company';
    }
}
