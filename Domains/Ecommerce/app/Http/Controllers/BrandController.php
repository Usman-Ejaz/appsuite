<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Brand\CreateRequest;
use Domains\Ecommerce\Http\Requests\Brand\UpdateRequest;
use Domains\Ecommerce\Http\Resources\BrandResource;
use Domains\Ecommerce\Repositories\BrandRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
class BrandController extends Controller
{
    public function __construct(protected BrandRepository $brands)
    {
        //
    }

    /**
     * Get Brands
     *
     * Returns the brands that belong to the company.
     */
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', EcommercePermission::BRAND_VIEW);

        $filters = $request->filters();

        $records = $this->brands->list($filters);

        return BrandResource::collection($records);
    }

    /**
     * Create Brand
     *
     * Creates a new brand for the company. The `slug` must be unique among the company's other brands.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $brand = $this->brands->create($input);

        return response()->json(['data' => BrandResource::make($brand)], 201);
    }

    /**
     * Get Brand
     *
     * Returns the details of a single brand.
     */
    public function get(GetResourceRequest $request, int $id): BrandResource
    {
        $this->authorize('permission', EcommercePermission::BRAND_VIEW);

        $filters = $request->filters();

        $record = $this->brands->get($id, $filters);

        return BrandResource::make($record);
    }

    /**
     * Update Brand
     *
     * Updates an existing brand. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id): BrandResource
    {
        $input = $request->validated();

        $record = $this->brands->update($id, $input);

        return BrandResource::make($record);
    }

    /**
     * Delete Brand
     *
     * Permanently removes a brand from the company.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::BRAND_DELETE);

        $this->brands->delete($id);

        return response()->json(null, 204);
    }
}
