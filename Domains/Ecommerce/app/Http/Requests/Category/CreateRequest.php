<?php

namespace Domains\Ecommerce\Http\Requests\Category;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::CATEGORY_MANAGE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The category's display name.
             *
             * @example Electronics
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * A url-friendly identifier for the category.
             *
             * @example electronics
             */
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories')->where('company_id', $this->user()?->getCompanyId())],

            /**
             * A short description of the category.
             *
             * @example Electronic devices and accessories.
             */
            'description' => ['nullable', 'string'],
        ];
    }
}
