<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Identity\Enums\CompanyStatus;
use Domains\Identity\Http\Requests\Company\CreateRequest;
use Domains\Identity\Http\Requests\Company\UpdateRequest;
use Domains\Identity\Http\Resources\CompanyCollection;
use Domains\Identity\Http\Resources\CompanyResource;
use Domains\Identity\Repositories\CompanyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Identity')]
class CompanyController extends Controller
{
    public function __construct(protected CompanyRepository $companies)
    {
        //
    }

    /**
     * Get Companies
     *
     * Returns every company on the platform. Restricted to root users.
     */
    public function list(GetCollectionRequest $request): CompanyCollection
    {
        abort_unless($request->user()->isRoot(), 403, 'Forbidden');

        $filters = $request->filters();

        $records = $this->companies->list($filters);

        return new CompanyCollection($records);
    }

    /**
     * Create Company
     *
     * Creates a new company. Restricted to root users. The `slug` must be unique across the
     * platform. The company starts with an Active status.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $company = $this->companies->create([...$input, 'status' => CompanyStatus::ACTIVE]);

        return response()->json(['data' => CompanyResource::make($company)], 201);
    }

    /**
     * Get Company
     *
     * Returns the details of a single company. Restricted to root users.
     */
    public function get(GetResourceRequest $request, int $id): CompanyResource
    {
        abort_unless($request->user()->isRoot(), 403);

        $filters = $request->filters();

        $record = $this->companies->get($id, $filters);

        return CompanyResource::make($record);
    }

    /**
     * Update Company
     *
     * Updates an existing company. Restricted to root users. Fields left out of the request
     * keep their current value.
     */
    public function update(UpdateRequest $request, int $id): CompanyResource
    {
        $input = $request->validated();

        $record = $this->companies->update($id, $input);

        return CompanyResource::make($record);
    }

    /**
     * Delete Company
     *
     * Permanently removes a company and cascades to every record scoped to it (users, roles,
     * API keys, and all other company-owned data). Restricted to root users.
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->isRoot(), 403);

        $this->companies->delete($id);

        return response()->json(null, 204);
    }
}
