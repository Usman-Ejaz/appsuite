<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Campaign\CreateRequest;
use Domains\Ecommerce\Http\Requests\Campaign\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CampaignCollection;
use Domains\Ecommerce\Http\Resources\CampaignResource;
use Domains\Ecommerce\Repositories\CampaignRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Campaigns')]
class CampaignController extends Controller
{
    public function __construct(protected CampaignRepository $campaigns)
    {
        //
    }

    /**
     * Get Campaigns
     *
     * Returns a paginated list of campaigns for the authenticated company.
     */
    public function list(Request $request): CampaignCollection
    {
        $this->authorize('permission', EcommercePermission::CAMPAIGN_VIEW->value);

        return new CampaignCollection($this->campaigns->filter($request->query())->list());
    }

    /**
     * Create Campaign
     *
     * Creates a new marketing campaign that can optionally be scheduled with a
     * start and end date.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $campaign = $this->campaigns->create($request->validated());

        return response()->json(['data' => CampaignResource::make($campaign)], 201);
    }

    /**
     * Get Campaign
     *
     * Retrieves a single campaign.
     */
    public function get(int $id): CampaignResource
    {
        $this->authorize('permission', EcommercePermission::CAMPAIGN_VIEW->value);

        return CampaignResource::make($this->campaigns->findOrFail($id));
    }

    /**
     * Update Campaign
     *
     * Updates a campaign's name, slug, description, status, or schedule.
     */
    public function update(UpdateRequest $request, int $id): CampaignResource
    {
        return CampaignResource::make($this->campaigns->update($id, $request->validated()));
    }

    /**
     * Delete Campaign
     *
     * Permanently removes a campaign.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::CAMPAIGN_DELETE->value);

        $this->campaigns->delete($id);

        return response()->json(null, 204);
    }
}
