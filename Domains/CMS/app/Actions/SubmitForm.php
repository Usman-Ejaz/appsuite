<?php

namespace Domains\CMS\Actions;

use Domains\CMS\Enums\FormSubmissionStatus;
use Domains\CMS\Models\Form;
use Domains\CMS\Models\FormSubmission;

class SubmitForm
{
    public function handle(Form $form, array $data, ?string $ip, ?string $userAgent): FormSubmission
    {
        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'company_id' => $form->company_id,
            'data' => $data,
            'status' => FormSubmissionStatus::NEW,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        // TODO(form-actions): execute this form's active FormAction rows against $submission.
        // Deferred pending a follow-up design session — expected shape: a handler registry
        // keyed by FormAction::type (e.g. send_email, call_webhook), dispatched via a queued
        // job so a slow/broken third-party call never blocks this response, with a
        // run-tracking table so failures are diagnosable rather than silent. Do not run
        // handlers inline/synchronously here once they exist.

        return $submission;
    }
}
