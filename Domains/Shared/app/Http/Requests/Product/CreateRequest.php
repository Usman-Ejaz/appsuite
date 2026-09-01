<?php

namespace Domains\Shared\Http\Requests\Product;

use Domains\Shared\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appCode = $this->route('app_code');

        return Gate::allows('permission', "{$appCode}:products:create");
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        return [
            /**
             * The product's display name.
             *
             * @example Wireless Mouse
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * A URL-friendly identifier for the product, unique per company.
             *
             * @example wireless-mouse
             */
            'slug' => ['required', 'string', 'max:255', Rule::unique('products')->where('company_id', $companyId)],

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
             *
             * @default true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * Whether the product should be highlighted as featured.
             *
             * @example false
             *
             * @default false
             */
            'is_featured' => ['nullable', 'boolean'],

            /**
             * The page title used for search engines, if different from `name`.
             *
             * @example Wireless Mouse | Acme Store
             */
            'meta_title' => ['nullable', 'string', 'max:255'],

            /**
             * The page description used for search engines.
             *
             * @example Shop the ergonomic wireless mouse with adjustable DPI at Acme Store.
             */
            'meta_description' => ['nullable', 'string'],

            /**
             * The canonical URL for this product's page, used to avoid duplicate-content
             * issues with search engines.
             *
             * @example https://store.example.com/products/wireless-mouse
             */
            'canonical_url' => ['nullable', 'string', 'max:2048'],

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
            'sku' => ['required', 'string', 'max:100', Rule::unique('products')->where('company_id', $companyId)],

            /**
             * What the product costs the company to acquire or produce.
             *
             * @example 12.50
             */
            'cost_price' => ['nullable', 'numeric', 'min:0'],

            /**
             * The product's selling price.
             *
             * @example 29.99
             */
            'selling_price' => ['required', 'numeric', 'min:0'],

            /**
             * An original price shown alongside `selling_price` for a sale display, used when
             * it is higher than `selling_price`.
             *
             * @example 39.99
             */
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],

            /**
             * The three-letter ISO 4217 currency code for the product's monetary amounts.
             *
             * @example USD
             *
             * @default USD
             */
            'currency' => ['nullable', 'string', 'size:3'],

            /**
             * The number of units currently in stock.
             *
             * @example 100
             *
             * @default 0
             */
            'stock_quantity' => ['nullable', 'integer', 'min:0'],

            /**
             * The stock level at or below which the product should be reordered.
             *
             * @example 10
             */
            'reorder_threshold' => ['nullable', 'integer', 'min:0'],

            /**
             * Whether `stock_quantity` limits how many units can be ordered. When set to
             * `false`, the product can be ordered regardless of `stock_quantity`.
             *
             * @example true
             *
             * @default true
             */
            'track_inventory' => ['nullable', 'boolean'],

            /**
             * The product's stage in its selling lifecycle.
             *
             * @example Active
             *
             * @default Draft
             */
            'status' => ['nullable', Rule::enum(ProductStatus::class)],

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
