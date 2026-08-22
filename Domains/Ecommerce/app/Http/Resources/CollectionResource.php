<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\Collection;
use Illuminate\Http\Request;

/**
 * @mixin Collection
 */
class CollectionResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.collections';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,

            /**
             * The collection's display name.
             */
            'name' => $this->name,

            /**
             * A unique, URL-friendly identifier for the collection.
             */
            'slug' => $this->slug,

            /**
             * A longer description of the collection.
             */
            'description' => $this->description,

            /**
             * The URL of an image representing the collection.
             */
            'image' => $this->image,

            /**
             * Whether the collection is visible and available for use.
             */
            'is_active' => $this->is_active,

            /**
             * The products in this collection, ordered by their display position.
             */
            'products' => EcommerceProductResource::collection($this->whenLoaded('products')),
        ]);
    }
}
