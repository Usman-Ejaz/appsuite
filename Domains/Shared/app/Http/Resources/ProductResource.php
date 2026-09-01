<?php

namespace Domains\Shared\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Shared\Models\Product;
use Illuminate\Http\Request;

/**
 * @mixin Product
 */
class ProductResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $this->routeName = "api.{$this->app_code}.products";

        return array_merge(parent::toArray($request), [
            'company_id' => $this->whenHas('company_id'),

            /**
             * The app this product belongs to.
             *
             * @example ecommerce
             */
            'app_code' => $this->whenHas('app_code'),

            /**
             * The product's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * A URL-friendly identifier for the product, unique per company.
             */
            'slug' => $this->whenHas('slug'),

            /**
             * A longer description of the product.
             */
            'description' => $this->whenHas('description'),

            /**
             * The URL of an image representing the product.
             *
             * @example https://example.com/images/wireless-mouse.jpg
             */
            'thumbnail' => $this->whenHas('thumbnail'),

            /**
             * Whether the product is active and visible.
             */
            'is_active' => $this->whenHas('is_active'),

            /**
             * Whether the product is highlighted as featured.
             */
            'is_featured' => $this->whenHas('is_featured'),

            /**
             * The page title used for search engines, if different from `name`.
             */
            'meta_title' => $this->whenHas('meta_title'),

            /**
             * The page description used for search engines.
             */
            'meta_description' => $this->whenHas('meta_description'),

            /**
             * The canonical URL for this product's page.
             */
            'canonical_url' => $this->whenHas('canonical_url'),

            'brand_id' => $this->whenHas('brand_id'),

            /**
             * The brand assigned to the product, if any. Exposed as the raw related record
             * (rather than a domain-specific resource) since Brand is an Ecommerce-owned
             * concept and this resource is shared across every app.
             */
            'brand' => $this->whenLoaded('brand'),

            'category_id' => $this->whenHas('category_id'),

            /**
             * The category assigned to the product, if any.
             */
            'category' => CategoryResource::make($this->whenLoaded('category')),

            /**
             * The product's stock keeping unit, unique per company.
             *
             * @example SKU-1234-BLK
             */
            'sku' => $this->whenHas('sku'),

            /**
             * What the product costs the company to acquire or produce.
             *
             * @example 12.50
             */
            'cost_price' => $this->whenHas('cost_price'),

            /**
             * The product's selling price.
             *
             * @example 29.99
             */
            'selling_price' => $this->whenHas('selling_price'),

            /**
             * An original price shown alongside `selling_price` for a sale display, present
             * when it is higher than `selling_price`.
             *
             * @example 39.99
             */
            'compare_at_price' => $this->whenHas('compare_at_price'),

            /**
             * The three-letter ISO 4217 currency code for the product's monetary amounts.
             *
             * @example USD
             */
            'currency' => $this->whenHas('currency'),

            /**
             * The number of units currently in stock.
             *
             * @example 100
             */
            'stock_quantity' => $this->whenHas('stock_quantity'),

            /**
             * The stock level at or below which the product should be reordered.
             *
             * @example 10
             */
            'reorder_threshold' => $this->whenHas('reorder_threshold'),

            /**
             * Whether `stock_quantity` limits how many units can be ordered. When `false`, the
             * product can be ordered regardless of `stock_quantity`.
             */
            'track_inventory' => $this->whenHas('track_inventory'),

            /**
             * The product's stage in its selling lifecycle.
             */
            'status' => $this->whenHas('status'),

            /**
             * A freeform array of strings used for search and filtering.
             */
            'tags' => $this->whenHas('tags'),
        ]);
    }
}
