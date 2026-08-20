<?php

namespace Domains\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cms:forms:update');
    }

    /**
     * TODO(form-actions): once a handler registry exists, validate `type` against
     * Rule::in(array_keys(config('cms.form_action_handlers'))) and merge in that
     * handler's own per-type config rules, instead of accepting any string/array here.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:255'],
            'config' => ['required', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
