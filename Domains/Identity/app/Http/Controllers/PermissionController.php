<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Identity\Http\Requests\Permission\GetCollectionRequest;
use Domains\Identity\Http\Requests\Permission\GetResourceRequest;
use Domains\Identity\Http\Resources\PermissionResource;
use Domains\Identity\Repositories\PermissionRepository;

/**
 * Permissions are a fixed, code-defined catalog seeded per app (see the per-domain
 * `*Permission` enums) — this endpoint is read-only by design, not a content-management CRUD.
 */
#[Group('Identity')]
class PermissionController extends Controller
{
    public function __construct(protected PermissionRepository $permissions)
    {
        //
    }

    /**
     * Get Permissions
     *
     * Returns the platform's permission catalog, optionally filtered by `app_id`. Restricted
     * to root and owner users.
     */
    public function list(GetCollectionRequest $request)
    {
        abort_unless($request->user()->isRoot() || $request->user()->isOwner(), 403);

        $filters = $request->filters();

        $records = $this->permissions->list($filters);

        return PermissionResource::collection($records);
    }

    /**
     * Get Permission
     *
     * Returns the details of a single permission. Restricted to root and owner users.
     */
    public function get(GetResourceRequest $request, int $id): PermissionResource
    {
        abort_unless($request->user()->isRoot() || $request->user()->isOwner(), 403);

        $filters = $request->filters();

        $record = $this->permissions->get($id, $filters);

        return PermissionResource::make($record);
    }
}
