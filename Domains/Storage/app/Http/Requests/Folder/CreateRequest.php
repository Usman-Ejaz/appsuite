<?php

namespace Domains\Storage\Http\Requests\Folder;

use Domains\Storage\Models\Folder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],

            /**
             * The parent folder to nest this folder under, if any.
             *
             * @example 3
             */
            'parent_id' => ['nullable', 'integer', Rule::exists(Folder::class, 'id')->where('company_id', $companyId)],
        ];
    }
}
