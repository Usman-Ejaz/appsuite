<?php

namespace Domains\CMS\Http\Requests\Form;

use Domains\CMS\Enums\CmsPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CmsPermission::UpdateForms->value);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('forms')->where('company_id', $this->user()->getCompanyId())->ignore($this->route('form'))],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'success_message' => ['nullable', 'string', 'max:500'],
            'redirect_url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
