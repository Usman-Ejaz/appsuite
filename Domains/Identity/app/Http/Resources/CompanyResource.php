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
        return array_merge(parent::toArray($request), [
            /**
             * The company's display name.
             */
            'name' => $this->name,

            /**
             * A url-friendly identifier for the company.
             */
            'slug' => $this->slug,

            /**
             * A short description of the company.
             */
            'description' => $this->description,

            /**
             * The company's platform status.
             */
            'status' => $this->status,

            /**
             * The company's mailing or business address.
             */
            'address' => $this->address,

            /**
             * The company's registered business license number.
             */
            'license_number' => $this->license_number,

            /**
             * When the company joined the platform.
             */
            'joined_at' => $this->joined_at,

            /**
             * The apps this company is subscribed to.
             *
             * @var AppResource[]
             */
            'apps' => AppResource::collection($this->whenLoaded('apps')),
        ]);
    }
}
