<?php

namespace Domains\Ecommerce\Http\Requests\EcommerceProduct;

use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Enums\EcommerceProductStatus;
use Domains\Ecommerce\Models\EcommerceProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', EcommercePermission::PRODUCT_UPDATE->value);
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        // The route's {id} identifies the EcommerceProduct, but the slug's
        // uniqueness lives on the parent Product row — resolve its id
        // separately so the unique check ignores the right primary key.
        $productId = EcommerceProduct::query()->whereKey($this->route('id'))->value('product_id');

        return [
            /**
             * The product's display name.
             *
             * @example Wireless Mouse
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * A URL-friendly identifier for the product, unique per company.
             *
             * @example wireless-mouse
             */
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products')->where('company_id', $companyId)->ignore($productId)],

            /**
             * A longer description of the product.
             *
             * @example An ergonomic wireless mouse with adjustable DPI.
             */
            'description' => ['nullable', 'string'],

            /**
             * The URL of an image representing the product.
             *
             * @example https://example.com/images/wireless-mouse.jpg
             */
            'thumbnail' => ['nullable', 'string', 'max:255'],

            /**
             * Whether the product is active and visible.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * The brand this product belongs to.
             *
             * @example 5
             */
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')->where('company_id', $companyId)],

            /**
             * The category this product belongs to.
             *
             * @example 12
             */
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('company_id', $companyId)],

            /**
             * The product's stock keeping unit, unique per company.
             *
             * @example SKU-1234-BLK
             */
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('ecommerce_products')->where('company_id', $companyId)->ignore($this->route('id'))],

            /**
             * The product's selling price.
             *
             * @example 29.99
             */
            'price' => ['sometimes', 'numeric', 'min:0'],

            /**
             * An original price shown alongside `price` for a sale display, used when it is
             * higher than `price`.
             *
             * @example 39.99
             */
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],

            /**
             * The three-letter ISO 4217 currency code for the product's monetary amounts.
             *
             * @example USD
             */
            'currency' => ['nullable', 'string', 'size:3'],

            /**
             * The number of units currently in stock.
             *
             * @example 100
             */
            'stock_quantity' => ['nullable', 'integer', 'min:0'],

            /**
             * Whether `stock_quantity` limits how many units can be ordered. When set to
             * `false`, the product can be ordered regardless of `stock_quantity`.
             *
             * @example true
             */
            'track_inventory' => ['nullable', 'boolean'],

            /**
             * The product's stage in its selling lifecycle.
             *
             * @example Active
             */
            'status' => ['nullable', Rule::enum(EcommerceProductStatus::class)],

            /**
             * A freeform array of strings used for search and filtering.
             *
             * @example ["sale", "bestseller"]
             */
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ];
    }
}
