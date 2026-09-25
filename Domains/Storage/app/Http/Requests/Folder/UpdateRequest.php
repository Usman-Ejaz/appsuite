<?php

namespace Domains\Storage\Http\Requests\Folder;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        return [
            /**
             * The folder's display name.
             *
             * @example Product Photos
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * The parent folder to nest this folder under, if any. A folder cannot be
             * nested under itself.
             *
             * @example 3
             */
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('folders', 'id')->where('company_id', $companyId),
                function (string $attribute, mixed $value, Closure $fail) {
                    if ((int) $value === (int) $this->route('id')) {
                        $fail('A folder cannot be nested under itself.');
                    }
                },
            ],
        ];
    }
}
