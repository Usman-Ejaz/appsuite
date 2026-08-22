<?php

namespace Domains\Ecommerce\Http\Requests\Review;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::REVIEW_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            /**
             * The star rating given to the product, from 1 to 5.
             *
             * @example 4
             */
            'rating' => ['sometimes', 'integer', 'between:1,5'],

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
