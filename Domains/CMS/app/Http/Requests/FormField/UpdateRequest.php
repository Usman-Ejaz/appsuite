<?php

namespace Domains\CMS\Http\Requests\FormField;

use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Enums\FormFieldType;
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
            'label' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('form_fields')->where('form_id', $this->route('form'))->ignore($this->route('id'))],
            'type' => ['sometimes', Rule::enum(FormFieldType::class)],
            'options' => ['nullable', 'array'],
            'default_value' => ['nullable'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'validation_rules' => ['nullable', 'array'],
        ];
    }
}
