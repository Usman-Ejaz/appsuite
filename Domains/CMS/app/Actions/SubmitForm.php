<?php

namespace Domains\CMS\Actions;

use Domains\CMS\Enums\CMSTrigger;
use Domains\CMS\Enums\FormSubmissionStatus;
use Domains\CMS\Models\Form;
use Domains\CMS\Models\FormSubmission;
use Domains\Notification\Actions\TriggerFormSubmissionAction;

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

        // Notifications run as a queued trigger action, never inline here. TODO(form-actions):
        // this form's FormAction rows still aren't executed — they move onto `Cms:Form:Submitted`
        // trigger actions with a form condition.
        TriggerFormSubmissionAction::dispatch($submission, CMSTrigger::FORM_SUBMITTED);

        return $submission;
    }
}
