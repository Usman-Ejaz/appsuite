<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\Review;
use Illuminate\Http\Request;

/**
 * @mixin Review
 */
class ReviewResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.reviews';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,
            'ecommerce_product_id' => $this->ecommerce_product_id,

            /**
             * The product this review was left on.
             */
            'product' => EcommerceProductResource::make($this->whenLoaded('ecommerceProduct')),

            'customer_id' => $this->customer_id,

            /**
             * The customer who left the review, if any.
             */
            'customer' => CustomerResource::make($this->whenLoaded('customer')),

            /**
             * The star rating given to the product, from 1 to 5.
             *
             * @example 4
             */
            'rating' => $this->rating,

            'title' => $this->title,
            'body' => $this->body,

            /**
             * The review's moderation state.
             */
            'status' => $this->status,
        ]);
    }
}
