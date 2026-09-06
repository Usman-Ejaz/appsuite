<?php

namespace Domains\Identity\Http\Resources;

use Domains\Core\Http\Resources\AppResource;
use Domains\Core\Http\Resources\BaseResource;
use Domains\Identity\Models\Company;
use Illuminate\Http\Request;

/**
 * @mixin Company
 */
class CompanyResource extends BaseResource
{
    public string $routeName = 'api.identity.companies';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            /**
             * The company's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * A url-friendly identifier for the company.
             */
            'slug' => $this->whenHas('slug'),

            /**
             * A short description of the company.
             */
            'description' => $this->whenHas('description'),

            /**
             * The company's platform status.
             */
            'status' => $this->whenHas('status'),

            /**
             * The company's mailing or business address.
             */
            'address' => $this->whenHas('address'),

            /**
             * The company's registered business license number.
             */
            'license_number' => $this->whenHas('license_number'),

            /**
             * When the company joined the platform.
             */
            'joined_at' => $this->whenHas('joined_at'),

            /**
             * The apps this company is subscribed to.
             *
             * @var AppResource[]
             */
            'apps' => AppResource::collection($this->whenLoaded('apps')),

            $this->merge(parent::toArray($request)),
        ];
    }
}
