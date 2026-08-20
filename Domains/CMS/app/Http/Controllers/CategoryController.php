<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Http\Requests\CreateCategoryRequest;
use Domains\CMS\Http\Requests\UpdateCategoryRequest;
use Domains\CMS\Http\Resources\CategoryCollection;
use Domains\CMS\Http\Resources\CategoryResource;
use Domains\Shared\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function list(Request $request): CategoryCollection
    {
        abort_unless($request->user()?->can('cms:categories:view'), 403);

        $categories = Category::query()
            ->where('company_id', $request->user()->getCompanyId())
            ->latest()
            ->get();

        return new CategoryCollection($categories);
    }

    public function create(CreateCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json(['data' => new CategoryResource($category)], 201);
    }

    public function get(Request $request, int $id): CategoryResource
    {
        abort_unless($request->user()?->can('cms:categories:view'), 403);

        $category = Category::query()
            ->where('company_id', $request->user()->getCompanyId())
            ->findOrFail($id);

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, int $id): CategoryResource
    {
        $category = Category::query()
            ->where('company_id', $request->user()->getCompanyId())
            ->findOrFail($id);

        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('cms:categories:manage'), 403);

        Category::query()
            ->where('company_id', $request->user()->getCompanyId())
            ->findOrFail($id)
            ->delete();

        return response()->json(null, 204);
    }
}
