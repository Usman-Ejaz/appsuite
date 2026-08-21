<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\Category\CreateRequest;
use Domains\CMS\Http\Requests\Category\UpdateRequest;
use Domains\CMS\Http\Resources\CategoryCollection;
use Domains\CMS\Http\Resources\CategoryResource;
use Domains\CMS\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected CategoryRepository $categories)
    {
        //
    }

    public function list(Request $request): CategoryCollection
    {
        $this->authorize('permission', CmsPermission::ViewCategories->value);

        return new CategoryCollection($this->categories->filter($request->query())->list());
    }

    public function create(CreateRequest $request): JsonResponse
    {
        $category = $this->categories->create($request->validated());

        return response()->json(['data' => CategoryResource::make($category)], 201);
    }

    public function get(int $id): CategoryResource
    {
        $this->authorize('permission', CmsPermission::ViewCategories->value);

        return CategoryResource::make($this->categories->findOrFail($id));
    }

    public function update(UpdateRequest $request, int $id): CategoryResource
    {
        return CategoryResource::make($this->categories->update($id, $request->validated()));
    }

    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', CmsPermission::ManageCategories->value);

        $this->categories->delete($id);

        return response()->json(null, 204);
    }
}
