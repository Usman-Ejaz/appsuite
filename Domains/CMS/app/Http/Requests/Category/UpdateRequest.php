<?php

namespace Domains\CMS\Http\Requests\Category;

use Domains\CMS\Enums\CmsPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CmsPermission::ManageCategories->value);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('categories')->where('company_id', $this->user()?->getCompanyId())->ignore($this->route('id'))],
            'description' => ['nullable', 'string'],
        ];
    }
}
