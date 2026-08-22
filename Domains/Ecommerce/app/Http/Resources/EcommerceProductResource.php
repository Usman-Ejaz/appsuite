<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\EcommerceProduct;
use Illuminate\Http\Request;

/**
 * @mixin EcommerceProduct
 */
class EcommerceProductResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.products';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,
            'product_id' => $this->product_id,

            /**
             * The product's display name.
             */
            'name' => $this->whenLoaded('product', fn () => $this->product->name),

            /**
             * A URL-friendly identifier for the product, unique per company.
             */
            'slug' => $this->whenLoaded('product', fn () => $this->product->slug),

            /**
             * A longer description of the product.
             */
            'description' => $this->whenLoaded('product', fn () => $this->product->description),

            /**
             * The URL of an image representing the product.
             *
             * @example https://example.com/images/wireless-mouse.jpg
             */
            'thumbnail' => $this->whenLoaded('product', fn () => $this->product->thumbnail),

            /**
             * Whether the product is active and visible.
             */
            'is_active' => $this->whenLoaded('product', fn () => $this->product->is_active),

            'brand_id' => $this->brand_id,

            /**
             * The brand assigned to the product, if any.
             */
            'brand' => BrandResource::make($this->whenLoaded('brand')),

            'category_id' => $this->category_id,

            /**
             * The category assigned to the product, if any.
             */
            'category' => CategoryResource::make($this->whenLoaded('category')),

            /**
             * The product's stock keeping unit, unique per company.
             *
             * @example SKU-1234-BLK
             */
            'sku' => $this->sku,

            /**
             * The product's selling price.
             *
             * @example 29.99
             */
            'price' => $this->price,

            /**
             * An original price shown alongside `price` for a sale display, present when it is
             * higher than `price`.
             *
             * @example 39.99
             */
            'compare_at_price' => $this->compare_at_price,

            /**
             * The three-letter ISO 4217 currency code for the product's monetary amounts.
             *
             * @example USD
             */
            'currency' => $this->currency,

            /**
             * The number of units currently in stock.
             *
             * @example 100
             */
            'stock_quantity' => $this->stock_quantity,

            /**
             * Whether `stock_quantity` limits how many units can be ordered. When `false`, the
             * product can be ordered regardless of `stock_quantity`.
             */
            'track_inventory' => $this->track_inventory,

            /**
             * The product's stage in its selling lifecycle.
             */
            'status' => $this->status,

            /**
             * A freeform array of strings used for search and filtering.
             */
            'tags' => $this->tags,
        ]);
    }
}
