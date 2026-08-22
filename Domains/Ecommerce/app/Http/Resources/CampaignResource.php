<?php

namespace Domains\Ecommerce\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Ecommerce\Models\Campaign;
use Illuminate\Http\Request;

/**
 * @mixin Campaign
 */
class CampaignResource extends BaseResource
{
    public string $routeName = 'api.ecommerce.campaigns';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,

            /**
             * The campaign's display name.
             */
            'name' => $this->name,

            /**
             * A unique, URL-friendly identifier for the campaign.
             */
            'slug' => $this->slug,

            /**
             * A longer description of the campaign.
             */
            'description' => $this->description,

            /**
             * The campaign's current publication state.
             */
            'status' => $this->status,

            /**
             * When the campaign starts running.
             */
            'starts_at' => $this->starts_at,

            /**
             * When the campaign stops running.
             */
            'ends_at' => $this->ends_at,
        ]);
    }
}
