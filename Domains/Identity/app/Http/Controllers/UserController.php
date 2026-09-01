<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Identity\Http\Requests\User\CreateRequest;
use Domains\Identity\Http\Requests\User\GetCollectionRequest;
use Domains\Identity\Http\Requests\User\GetResourceRequest;
use Domains\Identity\Http\Requests\User\UpdateRequest;
use Domains\Identity\Http\Resources\UserCollection;
use Domains\Identity\Http\Resources\UserResource;
use Domains\Identity\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Identity')]
class UserController extends Controller
{
    public function __construct(protected UserRepository $users)
    {
        //
    }

    /**
     * Get Users
     *
     * Returns the users on the platform. A root user sees every user; an owner sees only the
     * users within their own company. Restricted to root and owner users.
     */
    public function list(GetCollectionRequest $request): UserCollection
    {
        $actor = $request->user();

        abort_unless($actor->isRoot() || $actor->isOwner(), 403);

        $filters = $request->filters();

        $records = $this->users->list($filters);

        return new UserCollection($records);
    }

    /**
     * Create User
     *
     * Creates a new user. Restricted to root and owner users. An owner's new user is always
     * created into the owner's own company, regardless of any `company_id` in the request. A
     * root user may target any company via `company_id`, or omit it to default to root's own
     * company.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $actor = $request->user();
        $input = $request->validated();

        // array_key_exists distinguishes an explicit `company_id: null` (create a user with no
        // company) from the field being omitted entirely (default to root's own company) — `??`
        // cannot tell those apart, since both look like "absent" to it.
        $input['company_id'] = $actor->isRoot()
            ? (array_key_exists('company_id', $input) ? $input['company_id'] : $actor->company_id)
            : $actor->company_id;

        $user = $this->users->create($input);

        return response()->json(['data' => UserResource::make($user)], 201);
    }

    /**
     * Get User
     *
     * Returns the details of a single user. Restricted to root and owner users; an owner may
     * only view users within their own company.
     */
    public function get(GetResourceRequest $request, int $id): UserResource
    {
        $actor = $request->user();

        abort_unless($actor->isRoot() || $actor->isOwner(), 403);

        $filters = $request->filters();

        $record = $this->users
            ->scopeToCompany($actor->isRoot() ? null : $actor->company_id)
            ->get($id, $filters);

        return UserResource::make($record);
    }

    /**
     * Update User
     *
     * Updates an existing user. Restricted to root and owner users; an owner may only update
     * users within their own company. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id): UserResource
    {
        $actor = $request->user();

        $input = $request->validated();

        $record = $this->users
            ->scopeToCompany($actor->isRoot() ? null : $actor->company_id)
            ->update($id, $input);

        return UserResource::make($record);
    }

    /**
     * Delete User
     *
     * Permanently removes a user. Restricted to root and owner users; an owner may only delete
     * users within their own company.
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor->isRoot() || $actor->isOwner(), 403);

        $this->users->scopeToCompany($actor->isRoot() ? null : $actor->company_id)->delete($id);

        return response()->json(null, 204);
    }
}
