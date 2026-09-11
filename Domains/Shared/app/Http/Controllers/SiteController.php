<?php

namespace Domains\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Shared\Http\Requests\Site\CreateRequest;
use Domains\Shared\Http\Requests\Site\UpdateRequest;
use Domains\Shared\Http\Resources\SiteCollection;
use Domains\Shared\Http\Resources\SiteResource;
use Domains\Shared\Repositories\SiteRepository;
use Illuminate\Http\JsonResponse;

/**
 * Shared across every app: sites are a company-level concept (no app_code
 * scoping), so this controller/repository is registered once from the
 * Shared domain's own routes rather than per app.
 */
#[Group('Shared')]
class SiteController extends Controller
{
    public function __construct(protected SiteRepository $sites)
    {
        //
    }

    /**
     * Get Sites
     *
     * Returns a paginated list of sites for the authenticated company.
     */
    public function list(GetCollectionRequest $request): SiteCollection
    {
        $filters = $request->filters();

        $records = $this->sites->list($filters);

        return new SiteCollection($records);
    }

    /**
     * Create Site
     *
     * Creates a new site for the authenticated company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $site = $this->sites->create($input);

        return response()->json(['data' => SiteResource::make($site)], 201);
    }

    /**
     * Get Site
     *
     * Returns the details of a single site.
     */
    public function get(GetResourceRequest $request, int $id): SiteResource
    {
        $filters = $request->filters();

        $record = $this->sites->get($id, $filters);

        return SiteResource::make($record);
    }

    /**
     * Update Site
     *
     * Updates an existing site. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id): SiteResource
    {
        $input = $request->validated();

        $record = $this->sites->update($id, $input);

        return SiteResource::make($record);
    }

    /**
     * Delete Site
     *
     * Permanently removes a site from the company.
     */
    public function delete(int $id): JsonResponse
    {
        $this->sites->delete($id);

        return response()->json(null, 204);
    }
}
