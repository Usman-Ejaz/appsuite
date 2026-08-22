<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Shared\Models\Category;
use Illuminate\Http\Request;

/**
 * @mixin Category
 */
class CategoryResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.categories';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            /**
             * The identifier of the company the category belongs to.
             */
            'company_id' => $this->company_id,

            /**
             * The category's display name.
             */
            'name' => $this->name,

            /**
             * A url-friendly identifier for the category.
             */
            'slug' => $this->slug,

            /**
             * A short description of the category.
             */
            'description' => $this->description,
        ]);
    }
}
