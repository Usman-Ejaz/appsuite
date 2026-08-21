<?php

namespace Domains\CMS\Http\Requests\Form;

use Domains\CMS\Actions\BuildSubmissionValidationRules;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Models\Form;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CmsPermission::SubmitForms->value);
    }

    public function rules(): array
    {
        $form = Form::query()
            ->where('company_id', $this->user()->getCompanyId())
            ->where('is_active', true)
            ->findOrFail($this->route('form'));

        return array_merge(
            ['data' => ['required', 'array']],
            (new BuildSubmissionValidationRules)->handle($form),
        );
    }
}
