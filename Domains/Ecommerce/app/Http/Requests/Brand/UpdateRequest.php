<?php

namespace Domains\Ecommerce\Http\Requests\Brand;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::BRAND_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The brand's display name.
             *
             * @example Acme
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * A url-friendly identifier for the brand.
             *
             * @example acme
             */
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('brands')->where('company_id', $this->user()?->getCompanyId())->ignore($this->route('id'))],

            /**
             * A short description of the brand.
             *
             * @example A trusted name in outdoor equipment since 1990.
             */
            'description' => ['nullable', 'string'],

            /**
             * The web address of the brand's logo image.
             *
             * @example https://cdn.example.com/logos/acme.png
             */
            'logo' => ['nullable', 'string', 'max:255'],

            /**
             * Whether the brand is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * Whether the brand is featured.
             *
             * @example true
             *
             * @default true
             */
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
