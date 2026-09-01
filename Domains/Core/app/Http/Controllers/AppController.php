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

#[Group('Core')]
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

        $filters = $request->filters();

        $records = $this->apps->list($filters);

        return AppResource::collection($records);
    }

    /**
     * Create App
     *
     * Adds a new app to the platform catalog. Restricted to root users. The `slug` and `code`
     * must each be unique across the platform.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $app = $this->apps->create($input);

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

        $filters = $request->filters();

        $record = $this->apps->get($id, $filters);

        return AppResource::make($record);
    }

    /**
     * Update App
     *
     * Updates an existing app. Restricted to root users. Fields left out of the request keep
     * their current value.
     */
    public function update(UpdateRequest $request, int $id): AppResource
    {
        $input = $request->validated();

        $record = $this->apps->update($id, $input);

        return AppResource::make($record);
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
