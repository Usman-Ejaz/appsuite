<?php

namespace Domains\CMS\Http\Requests\FormAction;

use Domains\CMS\Enums\CmsPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CmsPermission::UpdateForms->value);
    }

    /**
     * TODO(form-actions): once a handler registry exists, validate `type` against
     * Rule::in(array_keys(config('cms.form_action_handlers'))) and merge in that
     * handler's own per-type config rules, instead of accepting any string/array here.
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:255'],
            'config' => ['sometimes', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
