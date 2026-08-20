<?php

namespace Domains\CMS\Http\Requests;

use Domains\CMS\Enums\FormFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFormFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cms:forms:update');
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:100', Rule::unique('form_fields')->where('form_id', $this->route('form'))],
            'type' => ['required', Rule::enum(FormFieldType::class)],
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
