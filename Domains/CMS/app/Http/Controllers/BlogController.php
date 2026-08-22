<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Actions\BlogPublisher;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\Blog\CreateRequest;
use Domains\CMS\Http\Requests\Blog\UpdateRequest;
use Domains\CMS\Http\Resources\BlogCollection;
use Domains\CMS\Http\Resources\BlogResource;
use Domains\CMS\Repositories\BlogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected BlogRepository $blogs
    ) {
        //
    }

    public function list(Request $request): BlogCollection
    {
        $this->authorize('permission', CmsPermission::ViewBlogs->value);

        return new BlogCollection($this->blogs->filter($request->query())->list());
    }

    public function create(CreateRequest $request): JsonResponse
    {
        $blog = $this->blogs->create($request->validated());

        return response()->json(['data' => BlogResource::make($blog)], 201);
    }

    public function get(int $id): BlogResource
    {
        $this->authorize('permission', CmsPermission::ViewBlogs->value);

        return BlogResource::make($this->blogs->findOrFail($id));
    }

    public function update(UpdateRequest $request, int $id): BlogResource
    {
        return BlogResource::make($this->blogs->update($id, $request->validated()));
    }

    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', CmsPermission::DeleteBlogs->value);

        $this->blogs->delete($id);

        return response()->json(null, 204);
    }

    public function publish(int $id): BlogResource
    {
        $this->authorize('permission', CmsPermission::PublishBlogs->value);

        $blog = BlogPublisher::publish(
            $this->blogs->findOrFail($id)
        );

        return BlogResource::make($blog);
    }

    public function unpublish(int $id): BlogResource
    {
        $this->authorize('permission', CmsPermission::PublishBlogs->value);

        $blog = BlogPublisher::unpublish(
            $this->blogs->findOrFail($id)
        );

        return BlogResource::make($blog);
    }
}
