<?php

namespace Domains\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cms:forms:update');
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
