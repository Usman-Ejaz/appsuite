<?php

namespace Domains\CMS\Http\Requests;

use Domains\CMS\Actions\BuildSubmissionValidationRules;
use Domains\CMS\Models\Form;
use Illuminate\Foundation\Http\FormRequest;

class SubmitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cms:forms:submit');
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
