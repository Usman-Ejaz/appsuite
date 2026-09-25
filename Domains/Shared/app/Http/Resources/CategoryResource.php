<?php

namespace Domains\Shared\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Shared\Models\Category;
use Illuminate\Http\Request;

/**
 * @mixin Category
 */
class CategoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $this->routeName = "api.{$this->app_code}.categories";

        return [
            'id' => $this->id,

            /**
             * The identifier of the company the category belongs to.
             */
            'company_id' => $this->whenHas('company_id'),

            /**
             * The app this category belongs to.
             *
             * @example ecommerce
             */
            'app_code' => $this->whenHas('app_code'),

            /**
             * The category's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * A url-friendly identifier for the category, unique per app within a company.
             */
            'slug' => $this->whenHas('slug'),

            /**
             * A short description of the category.
             */
            'description' => $this->whenHas('description'),

            /**
             * Whether the category is highlighted as featured.
             */
            'is_featured' => $this->whenHas('is_featured'),

            /**
             * The category's stage in its publishing lifecycle.
             */
            'status' => $this->whenHas('status'),

            'parent_id' => $this->whenHas('parent_id'),

            /**
             * The parent category, if any.
             */
            'parent' => static::make($this->whenLoaded('parent')),

            /**
             * The direct child categories, if loaded.
             */
            'children' => static::collection($this->whenLoaded('children')),

            $this->merge(parent::toArray($request)),
        ];
    }
}
