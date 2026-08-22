<?php

namespace Domains\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Ecommerce\Http\Requests\Category\CreateRequest;
use Domains\Ecommerce\Http\Requests\Category\UpdateRequest;
use Domains\Ecommerce\Http\Resources\CategoryCollection;
use Domains\Ecommerce\Http\Resources\CategoryResource;
use Domains\Ecommerce\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Ecommerce, Categories')]
class CategoryController extends Controller
{
    public function __construct(protected CategoryRepository $categories)
    {
        //
    }

    /**
     * Get Categories
     *
     * Returns the categories that belong to the company.
     */
    public function list(Request $request): CategoryCollection
    {
        $this->authorize('permission', EcommercePermission::CATEGORY_VIEW->value);

        return new CategoryCollection($this->categories->filter($request->query())->list());
    }

    /**
     * Create Category
     *
     * Creates a new category for the company. The `slug` must be unique among the company's other categories.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $category = $this->categories->create($request->validated());

        return response()->json(['data' => CategoryResource::make($category)], 201);
    }

    /**
     * Get Category
     *
     * Returns the details of a single category.
     */
    public function get(int $id): CategoryResource
    {
        $this->authorize('permission', EcommercePermission::CATEGORY_VIEW->value);

        return CategoryResource::make($this->categories->findOrFail($id));
    }

    /**
     * Update Category
     *
     * Updates an existing category. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id): CategoryResource
    {
        return CategoryResource::make($this->categories->update($id, $request->validated()));
    }

    /**
     * Delete Category
     *
     * Permanently removes a category from the company.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', EcommercePermission::CATEGORY_MANAGE->value);

        $this->categories->delete($id);

        return response()->json(null, 204);
    }
}
