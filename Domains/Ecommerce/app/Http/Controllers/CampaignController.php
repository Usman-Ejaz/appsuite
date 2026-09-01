<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Campaign\CreateRequest;
use Domains\Ecommerce\Http\Requests\Campaign\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CampaignResource;
use Domains\Ecommerce\Repositories\CampaignRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
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
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', EcommercePermission::CAMPAIGN_VIEW);

        $filters = $request->filters();

        $records = $this->campaigns->list($filters);

        return CampaignResource::collection($records);
    }

    /**
     * Create Campaign
     *
     * Creates a new marketing campaign that can optionally be scheduled with a
     * start and end date.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $campaign = $this->campaigns->create($input);

        return response()->json(['data' => CampaignResource::make($campaign)], 201);
    }

    /**
     * Get Campaign
     *
     * Retrieves a single campaign.
     */
    public function get(GetResourceRequest $request, int $id): CampaignResource
    {
        $this->authorize('permission', EcommercePermission::CAMPAIGN_VIEW);

        $filters = $request->filters();

        $record = $this->campaigns->get($id, $filters);

        return CampaignResource::make($record);
    }

    /**
     * Update Campaign
     *
     * Updates a campaign's name, slug, description, status, or schedule.
     */
    public function update(UpdateRequest $request, int $id): CampaignResource
    {
        $input = $request->validated();

        $record = $this->campaigns->update($id, $input);

        return CampaignResource::make($record);
    }

    /**
     * Delete Campaign
     *
     * Permanently removes a campaign.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::CAMPAIGN_DELETE);

        $this->campaigns->delete($id);

        return response()->json(null, 204);
    }
}
