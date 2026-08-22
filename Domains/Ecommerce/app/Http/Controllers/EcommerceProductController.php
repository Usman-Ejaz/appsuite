<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\EcommerceProduct\CreateRequest;
use Domains\Ecommerce\Http\Requests\EcommerceProduct\UpdateRequest;
use Domains\Ecommerce\Http\Resources\EcommerceProductCollection;
use Domains\Ecommerce\Http\Resources\EcommerceProductResource;
use Domains\Ecommerce\Repositories\EcommerceProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Products')]
class EcommerceProductController extends Controller
{
    public function __construct(protected EcommerceProductRepository $products)
    {
        //
    }

    /**
     * Get Products
     *
     * Returns a paginated list of products for the authenticated company.
     */
    public function list(Request $request): EcommerceProductCollection
    {
        $this->authorize('permission', EcommercePermission::PRODUCT_VIEW->value);

        return new EcommerceProductCollection($this->products->filter($request->query())->list());
    }

    /**
     * Create Product
     *
     * Creates a new product from a single request containing both its display information and
     * its commerce information. Returns the complete created product.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return response()->json(['data' => EcommerceProductResource::make($product)], 201);
    }

    /**
     * Get Product
     *
     * Retrieves a single product, including its brand and category.
     */
    public function get(int $id): EcommerceProductResource
    {
        $this->authorize('permission', EcommercePermission::PRODUCT_VIEW->value);

        return EcommerceProductResource::make($this->products->findOrFail($id));
    }

    /**
     * Update Product
     *
     * Updates a product's display information, commerce information, or both in a single
     * request. Returns the complete updated product.
     */
    public function update(UpdateRequest $request, int $id): EcommerceProductResource
    {
        return EcommerceProductResource::make($this->products->update($id, $request->validated()));
    }

    /**
     * Delete Product
     *
     * Deleting a product removes it entirely, not just its listing. Its price, stock, and other
     * commerce details are permanently removed as well.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::PRODUCT_DELETE->value);

        $this->products->delete($id);

        return response()->json(null, 204);
    }
}
