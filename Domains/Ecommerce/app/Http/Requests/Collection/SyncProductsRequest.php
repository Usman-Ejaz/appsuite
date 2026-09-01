<?php

namespace Domains\Ecommerce\Http\Requests\Collection;

use Domains\Ecommerce\Enums\EcommercePermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SyncProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::COLLECTION_UPDATE);
    }

    public function rules(): array
    {
        return [
            /**
             * The complete list of products to assign to the collection.
             *
             * @example [{"id": 101, "sort_order": 1}, {"id": 102, "sort_order": 2}]
             */
            'products' => ['present', 'array'],

            /**
             * The identifier of the product to include in the collection.
             *
             * @example 101
             */
            'products.*.id' => ['required', 'integer'],

            /**
             * The product's position within the collection. Lower numbers are shown first.
             *
             * @example 1
             */
            'products.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
