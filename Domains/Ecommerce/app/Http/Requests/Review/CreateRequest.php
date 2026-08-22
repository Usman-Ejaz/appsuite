<?php

namespace Domains\Ecommerce\Http\Requests\Review;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::REVIEW_CREATE->value);
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        return [
            /**
             * The product being reviewed.
             *
             * @example 17
             */
            'ecommerce_product_id' => ['required', 'integer', Rule::exists('ecommerce_products', 'id')->where('company_id', $companyId)],

            /**
             * The customer who left the review, if known.
             *
             * @example 42
             */
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where('company_id', $companyId)],

            /**
             * The star rating given to the product, from 1 to 5.
             *
             * @example 4
             */
            'rating' => ['required', 'integer', 'between:1,5'],

            /**
             * A short headline for the review.
             *
             * @example Exactly what I needed
             */
            'title' => ['nullable', 'string', 'max:255'],

            /**
             * The full text of the review.
             *
             * @example Works great and arrived quickly.
             */
            'body' => ['nullable', 'string'],
        ];
    }
}
