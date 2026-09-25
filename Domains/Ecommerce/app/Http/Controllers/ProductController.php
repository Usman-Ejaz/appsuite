<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Shared\Http\Requests\Product\CreateRequest;
use Domains\Shared\Http\Requests\Product\UpdateRequest;
use Domains\Shared\Http\Resources\ProductResource;
use Domains\Shared\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

/**
 * Shared across every app: one controller/repository serves ecommerce today, and any
 * future app's products, each scoped to its own app_code.
 */
#[Group('Ecommerce')]
class ProductController extends Controller
{
    public function __construct(protected ProductRepository $products)
    {
        //
    }

    /**
     * Get Products
     *
     * Returns a paginated list of products for the authenticated company.
     */
    public function list(GetCollectionRequest $request, string $app_code)
    {
        $this->authorize('permission', "{$app_code}:products:view");

        $filters = $request->filters();

        $records = $this->products->forAppCode($app_code)->list($filters);

        return ProductResource::collection($records);
    }

    /**
     * Create Product
     *
     * Creates a new product from a single request containing both its display information and
     * its commerce information. Returns the complete created product.
     */
    public function create(CreateRequest $request, string $app_code): JsonResponse
    {
        $input = $request->validated();

        $product = $this->products->forAppCode($app_code)->create($input);

        return response()->json(['data' => ProductResource::make($product)], 201);
    }

    /**
     * Get Product
     *
     * Retrieves a single product, including its brand and category.
     */
    public function get(GetResourceRequest $request, int $id, string $app_code): ProductResource
    {
        $this->authorize('permission', "{$app_code}:products:view");

        $filters = $request->filters();

        $record = $this->products->forAppCode($app_code)->get($id, $filters);

        return ProductResource::make($record);
    }

    /**
     * Update Product
     *
     * Updates a product's display information, commerce information, or both in a single
     * request. Returns the complete updated product.
     */
    public function update(UpdateRequest $request, int $id, string $app_code): ProductResource
    {
        $input = $request->validated();

        $record = $this->products->forAppCode($app_code)->update($id, $input);

        return ProductResource::make($record);
    }

    /**
     * Delete Product
     *
     * Deleting a product removes it entirely, not just its listing. Its price, stock, and other
     * commerce details are permanently removed as well.
     */
    public function delete(int $id, string $app_code): JsonResponse
    {
        $this->authorize('permission', "{$app_code}:products:delete");

        $this->products->forAppCode($app_code)->delete($id);

        return response()->json(null, 204);
    }
}
