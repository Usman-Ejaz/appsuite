<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Ecommerce\Actions\SyncCollectionProducts;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Collection\CreateRequest;
use Domains\Ecommerce\Http\Requests\Collection\SyncProductsRequest;
use Domains\Ecommerce\Http\Requests\Collection\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CollectionResource;
use Domains\Ecommerce\Repositories\CollectionRepository;
use Illuminate\Http\JsonResponse;

#[Group('Ecommerce')]
class CollectionController extends Controller
{
    public function __construct(
        protected CollectionRepository $collections,
        protected SyncCollectionProducts $syncCollectionProducts,
    ) {
        //
    }

    /**
     * Get Collections
     *
     * Returns a paginated list of collections for the authenticated company.
     */
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', EcommercePermission::COLLECTION_VIEW);

        $filters = $request->filters();

        $records = $this->collections->list($filters);

        return CollectionResource::collection($records);
    }

    /**
     * Create Collection
     *
     * Creates a new collection for grouping products. Add products to it afterward
     * using the sync products endpoint.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $collection = $this->collections->create($input);

        return response()->json(['data' => CollectionResource::make($collection)], 201);
    }

    /**
     * Get Collection
     *
     * Retrieves a single collection, including the products it contains.
     */
    public function get(GetResourceRequest $request, int $id): CollectionResource
    {
        $this->authorize('permission', EcommercePermission::COLLECTION_VIEW);

        $filters = $request->filters();

        $record = $this->collections->get($id, $filters);
        $record->load('products');

        return CollectionResource::make($record);
    }

    /**
     * Update Collection
     *
     * Updates a collection's name, slug, description, image, or active status.
     */
    public function update(UpdateRequest $request, int $id): CollectionResource
    {
        $input = $request->validated();

        $record = $this->collections->update($id, $input);

        return CollectionResource::make($record);
    }

    /**
     * Delete Collection
     *
     * Permanently removes a collection.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::COLLECTION_DELETE);

        $this->collections->delete($id);

        return response()->json(null, 204);
    }

    /**
     * Sync Collection Products
     *
     * Replaces the collection's entire product membership with the given list. This
     * is not additive: any product currently in the collection but missing from the
     * new list is removed, so sending a partial list will drop the rest. Use
     * `sort_order` to control how products are ordered within the collection.
     * Returns an error if a product does not belong to the current company.
     */
    public function syncProducts(SyncProductsRequest $request, int $id): CollectionResource
    {
        $collectionModel = $this->collections->findOrFail($id);

        $this->syncCollectionProducts->handle($collectionModel, $request->validated('products'));

        return CollectionResource::make($collectionModel->fresh('products'));
    }
}
