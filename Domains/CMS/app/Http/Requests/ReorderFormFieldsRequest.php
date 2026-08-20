<?php

namespace Domains\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderFormFieldsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cms:forms:update');
    }

    public function rules(): array
    {
        return [
            'fields' => ['required', 'array'],
            'fields.*.id' => ['required', 'integer'],
            'fields.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
