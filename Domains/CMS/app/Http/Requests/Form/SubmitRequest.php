<?php

namespace Domains\CMS\Http\Requests\Form;

use Domains\CMS\Actions\BuildSubmissionValidationRules;
use Domains\CMS\Enums\CMSPermission;
use Domains\CMS\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CMSPermission::FORM_SUBMIT);
    }

    public function rules(): array
    {
        $formId = $this->route('form_id');

        $form = $formId
            ? Form::query()
                ->where('company_id', $this->user()?->getCompanyId())
                ->active()
                ->findOrFail($formId)
            : null;

        return array_merge(
            ['data' => ['present', 'array']],
            $form ? (new BuildSubmissionValidationRules)->handle($form) : [],
        );
    }
}
