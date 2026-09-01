<?php

namespace Domains\CMS\Http\Requests\FormField;

use Domains\CMS\Enums\CmsPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CmsPermission::FORM_UPDATE->value);
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
