<?php

namespace Domains\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Shared\Http\Requests\Category\CreateRequest;
use Domains\Shared\Http\Requests\Category\UpdateRequest;
use Domains\Shared\Http\Resources\CategoryCollection;
use Domains\Shared\Http\Resources\CategoryResource;
use Domains\Shared\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared across every app: one controller/repository serves cms, ecommerce, and any
 * future app's categories, each scoped to its own app_code.
 */
#[Group('Shared')]
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
    public function list(GetCollectionRequest $request, string $app_code): CategoryCollection
    {
        $this->authorizeCategories($request, $app_code, 'view');

        $filters = $request->filters();

        $records = $this->categories->forAppCode($app_code)->list($filters);

        return new CategoryCollection($records);
    }

    /**
     * Create Category
     *
     * Creates a new category for the company. The `slug` must be unique among the company's
     * other categories within the same app.
     */
    public function create(CreateRequest $request, string $app_code): JsonResponse
    {
        $input = $request->validated();

        $category = $this->categories->forAppCode($app_code)->create($input);

        return response()->json(['data' => CategoryResource::make($category)], 201);
    }

    /**
     * Get Category
     *
     * Returns the details of a single category.
     */
    public function get(GetResourceRequest $request, int $id, string $app_code): CategoryResource
    {
        $this->authorizeCategories($request, $app_code, 'view');

        $filters = $request->filters();

        $record = $this->categories->forAppCode($app_code)->get($id, $filters);

        return CategoryResource::make($record);
    }

    /**
     * Update Category
     *
     * Updates an existing category. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id, string $app_code): CategoryResource
    {
        $input = $request->validated();

        $record = $this->categories->forAppCode($app_code)->update($id, $input);

        return CategoryResource::make($record);
    }

    /**
     * Delete Category
     *
     * Permanently removes a category from the company.
     */
    public function delete(Request $request, int $id, string $app_code): JsonResponse
    {
        $this->authorizeCategories($request, $app_code, 'manage');

        $this->categories->forAppCode($app_code)->delete($id);

        return response()->json(null, 204);
    }

    /**
     * Category permissions follow a uniform "{app_code}:categories:{view|manage}"
     * shape across every app (confirmed against both CmsPermission and
     * EcommercePermission's existing enum values), so no per-app enum is needed
     * here — the string is built directly from the route's app_code.
     */
    protected function authorizeCategories(Request $request, string $appCode, string $ability): void
    {
        $this->authorize('permission', "{$appCode}:categories:{$ability}");
    }
}
