<?php

namespace Domains\Storage\Http\Requests\Media;

use Domains\Storage\Enums\MediaCategory;
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
        return [
            /**
             * The classification of this media item.
             *
             * @example Gallery
             */
            'category' => ['nullable', Rule::enum(MediaCategory::class)],

            /**
             * The library folder to file this media item under, scoped to the authenticated
             * company. Pass null to unfile it.
             *
             * @example 3
             */
            'folder_id' => ['nullable', 'integer', Rule::exists('folders', 'id')->where('company_id', $this->user()?->getCompanyId())],

            /**
             * An optional title for the media item.
             *
             * @example Product Hero Shot
             */
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],

            /**
             * Whether the media item is starred.
             *
             * @example true
             */
            'starred' => ['sometimes', 'boolean'],
        ];
    }
}
