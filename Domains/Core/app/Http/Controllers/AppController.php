<?php

namespace Domains\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\App\CreateRequest;
use Domains\Core\Http\Requests\App\UpdateRequest;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Core\Http\Resources\AppResource;
use Domains\Core\Repositories\AppRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Core, Apps')]
class AppController extends Controller
{
    public function __construct(protected AppRepository $apps)
    {
        //
    }

    /**
     * Get Apps
     *
     * Returns the platform's app catalog. Restricted to root users.
     */
    public function list(GetCollectionRequest $request)
    {
        abort_unless($request->user()->isRoot(), 403);

        return AppResource::collection($this->apps->list($request->filters()));
    }

    /**
     * Create App
     *
     * Adds a new app to the platform catalog. Restricted to root users. The `slug` and `code`
     * must each be unique across the platform.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $app = $this->apps->create($request->validated());

        return response()->json(['data' => AppResource::make($app)], 201);
    }

    /**
     * Get App
     *
     * Returns the details of a single app. Restricted to root users.
     */
    public function get(GetResourceRequest $request, int $id): AppResource
    {
        abort_unless($request->user()->isRoot(), 403);

        return AppResource::make($this->apps->get($id, $request->filters()));
    }

    /**
     * Update App
     *
     * Updates an existing app. Restricted to root users. Fields left out of the request keep
     * their current value.
     */
    public function update(UpdateRequest $request, int $id): AppResource
    {
        return AppResource::make($this->apps->update($id, $request->validated()));
    }

    /**
     * Delete App
     *
     * Permanently removes an app from the catalog and its entitlements from every company it
     * was assigned to. Restricted to root users.
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->isRoot(), 403);

        $this->apps->delete($id);

        return response()->json(null, 204);
    }
}
