<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Brand\CreateRequest;
use Domains\Ecommerce\Http\Requests\Brand\UpdateRequest;
use Domains\Ecommerce\Http\Resources\BrandCollection;
use Domains\Ecommerce\Http\Resources\BrandResource;
use Domains\Ecommerce\Repositories\BrandRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Brands')]
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
    public function list(Request $request): BrandCollection
    {
        $this->authorize('permission', EcommercePermission::BRAND_VIEW->value);

        return new BrandCollection($this->brands->filter($request->query())->list());
    }

    /**
     * Create Brand
     *
     * Creates a new brand for the company. The `slug` must be unique among the company's other brands.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $brand = $this->brands->create($request->validated());

        return response()->json(['data' => BrandResource::make($brand)], 201);
    }

    /**
     * Get Brand
     *
     * Returns the details of a single brand.
     */
    public function get(int $id): BrandResource
    {
        $this->authorize('permission', EcommercePermission::BRAND_VIEW->value);

        return BrandResource::make($this->brands->findOrFail($id));
    }

    /**
     * Update Brand
     *
     * Updates an existing brand. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id): BrandResource
    {
        return BrandResource::make($this->brands->update($id, $request->validated()));
    }

    /**
     * Delete Brand
     *
     * Permanently removes a brand from the company.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::BRAND_DELETE->value);

        $this->brands->delete($id);

        return response()->json(null, 204);
    }
}
