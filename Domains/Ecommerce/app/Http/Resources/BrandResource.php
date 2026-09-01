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
        return [
            'id' => $this->id,

            /**
             * The identifier of the company the brand belongs to.
             */
            'company_id' => $this->whenHas('company_id'),

            /**
             * The brand's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * A url-friendly identifier for the brand.
             */
            'slug' => $this->whenHas('slug'),

            /**
             * A short description of the brand.
             */
            'description' => $this->whenHas('description'),

            /**
             * The web address of the brand's logo image.
             */
            'logo' => $this->whenHas('logo'),

            /**
             * Whether the brand is active.
             */
            'is_active' => $this->whenHas('is_active'),

            $this->merge(parent::toArray($request)),
        ];
    }
}
