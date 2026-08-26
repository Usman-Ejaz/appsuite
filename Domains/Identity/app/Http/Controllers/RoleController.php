<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Identity\Http\Requests\Role\CreateRequest;
use Domains\Identity\Http\Requests\Role\GetCollectionRequest;
use Domains\Identity\Http\Requests\Role\GetResourceRequest;
use Domains\Identity\Http\Requests\Role\UpdateRequest;
use Domains\Identity\Http\Resources\RoleCollection;
use Domains\Identity\Http\Resources\RoleResource;
use Domains\Identity\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Identity, Roles')]
class RoleController extends Controller
{
    public function __construct(protected RoleRepository $roles)
    {
        //
    }

    /**
     * Get Roles
     *
     * Returns the roles on the platform. A root user sees every role; an owner sees only the
     * roles within their own company. Restricted to root and owner users.
     */
    public function list(GetCollectionRequest $request): RoleCollection
    {
        $actor = $request->user();

        abort_unless($actor->isRoot() || $actor->isOwner(), 403);

        return new RoleCollection(
            $this->roles
                ->scopeToCompany($actor->isRoot() ? null : $actor->company_id)
                ->list($request->filters())
        );
    }

    /**
     * Create Role
     *
     * Creates a new role. Restricted to root and owner users. An owner's new role is always
     * created into the owner's own company, regardless of any `company_id` in the request. A
     * root user may target any company via `company_id`, or omit it to default to root's own
     * company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        // A root user can explicitly send `company_id: null` to create a system/platform role
        // (one that manages this admin area itself) even if their own account has a company —
        // array_key_exists distinguishes "explicitly null" from "field omitted", which `??`
        // could not (both look identical to `??`).
        $data['company_id'] = $actor->isRoot()
            ? (array_key_exists('company_id', $data) ? $data['company_id'] : $actor->company_id)
            : $actor->company_id;

        // Spatie's Role model defaults an unset guard_name to the currently active auth guard,
        // which on these API routes resolves to "sanctum" — but every other role in the system
        // (seeded data, the [company_id, name, guard_name] uniqueness check above) assumes
        // "web". Force it explicitly so a role created here is actually findable/unique
        // alongside the rest.
        $data['guard_name'] = 'web';

        $role = $this->roles->create($data);

        return response()->json(['data' => RoleResource::make($role)], 201);
    }

    /**
     * Get Role
     *
     * Returns the details of a single role. Restricted to root and owner users; an owner may
     * only view roles within their own company.
     */
    public function get(GetResourceRequest $request, int $id): RoleResource
    {
        $actor = $request->user();

        abort_unless($actor->isRoot() || $actor->isOwner(), 403);

        $role = $this->roles
            ->scopeToCompany($actor->isRoot() ? null : $actor->company_id)
            ->get($id, $request->filters());

        return RoleResource::make($role);
    }

    /**
     * Update Role
     *
     * Updates an existing role. Restricted to root and owner users; an owner may only update
     * roles within their own company. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id): RoleResource
    {
        $actor = $request->user();

        $role = $this->roles
            ->scopeToCompany($actor->isRoot() ? null : $actor->company_id)
            ->update($id, $request->validated());

        return RoleResource::make($role);
    }

    /**
     * Delete Role
     *
     * Permanently removes a role and revokes it from every user it was assigned to.
     * Restricted to root and owner users; an owner may only delete roles within their own
     * company.
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor->isRoot() || $actor->isOwner(), 403);

        $this->roles->scopeToCompany($actor->isRoot() ? null : $actor->company_id)->delete($id);

        return response()->json(null, 204);
    }
}
