<?php

namespace Domains\Notification\Actions;

use Domains\CMS\Models\FormSubmission;
use Domains\Notification\Services\Tokens\FormSubmissionTokenResolver;
use Domains\Notification\Services\Tokens\FormTokenResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Runs the trigger actions for a form submission.
 *
 * @property FormSubmission $resource
 */
class TriggerFormSubmissionAction extends AbstractTriggerAction
{
    protected function tokenResolvers(): array
    {
        $this->resource->loadMissing('form');

        return [
            new FormSubmissionTokenResolver($this->resource),
            new FormTokenResolver($this->resource->form),
        ];
    }

    /**
     * `submitter` is whoever filled in the form, reached through their `email` answer.
     */
    public function dynamicRecipients(string $code): Collection
    {
        $email = $this->resource->data['email'] ?? null;

        if ($code !== 'submitter' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return collect();
        }

        return collect([Notification::route('mail', $email)]);
    }
}
