<?php

namespace Domains\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cms:categories:manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('categories')->where('company_id', $this->user()->getCompanyId())->ignore($this->route('id'))],
            'description' => ['nullable', 'string'],
        ];
    }
}
