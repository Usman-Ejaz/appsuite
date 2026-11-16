<?php

namespace Domains\Notification\Services\Tokens;

/**
 * `{{ submission.* }}` tokens: every visible submission column. The answers are under
 * `{{ submission.data.<field> }}`, so a new form field is a new token with no code change.
 */
class FormSubmissionTokenResolver extends AbstractTokenResolver
{
    /**
     * Attributes never exposed as tokens.
     *
     * @var list<string>
     */
    protected array $except = ['company_id', 'creator_type', 'creator_id', 'updater_type', 'updater_id', 'user_agent'];

    public function prefix(): string
    {
        return 'submission';
    }
}
