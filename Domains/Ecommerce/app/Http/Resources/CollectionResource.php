<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\Collection;
use Domains\Shared\Http\Resources\ProductResource;
use Illuminate\Http\Request;

/**
 * @mixin Collection
 */
class CollectionResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.collections';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            'company_id' => $this->whenHas('company_id'),

            /**
             * The collection's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * A unique, URL-friendly identifier for the collection.
             */
            'slug' => $this->whenHas('slug'),

            /**
             * A longer description of the collection.
             */
            'description' => $this->whenHas('description'),

            /**
             * The URL of an image representing the collection.
             */
            'image' => $this->whenHas('image'),

            /**
             * Whether the collection is visible and available for use.
             */
            'is_active' => $this->whenHas('is_active'),

            /**
             * The products in this collection, ordered by their display position.
             */
            'products' => ProductResource::collection($this->whenLoaded('products')),

            $this->merge(parent::toArray($request)),
        ];
    }
}
