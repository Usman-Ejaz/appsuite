<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\Brand;
use Illuminate\Http\Request;

/**
 * @mixin Brand
 */
class BrandResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.brands';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            /**
             * The identifier of the company the brand belongs to.
             */
            'company_id' => $this->company_id,

            /**
             * The brand's display name.
             */
            'name' => $this->name,

            /**
             * A url-friendly identifier for the brand.
             */
            'slug' => $this->slug,

            /**
             * A short description of the brand.
             */
            'description' => $this->description,

            /**
             * The web address of the brand's logo image.
             */
            'logo' => $this->logo,

            /**
             * Whether the brand is active.
             */
            'is_active' => $this->is_active,
        ]);
    }
}
