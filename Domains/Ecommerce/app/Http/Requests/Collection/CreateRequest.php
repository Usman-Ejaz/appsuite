<?php

namespace Domains\Ecommerce\Http\Requests\Collection;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::COLLECTION_CREATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The collection's display name.
             *
             * @example Summer Sale
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * A unique, URL-friendly identifier for the collection.
             *
             * @example summer-sale
             */
            'slug' => ['required', 'string', 'max:255', Rule::unique('collections')->where('company_id', $this->user()?->getCompanyId())],

            /**
             * A longer description of the collection.
             *
             * @example Our best deals for the summer season.
             */
            'description' => ['nullable', 'string'],

            /**
             * The URL of an image representing the collection.
             *
             * @example https://cdn.example.com/collections/summer-sale.jpg
             */
            'image' => ['nullable', 'string', 'max:255'],

            /**
             * Whether the collection is visible and available for use.
             *
             * @example true
             *
             * @default true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
