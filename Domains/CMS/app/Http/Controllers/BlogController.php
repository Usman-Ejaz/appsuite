<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\CMS\Actions\PublishBlog;
use Domains\CMS\Actions\UnpublishBlog;
use Domains\CMS\Http\Requests\CreateBlogRequest;
use Domains\CMS\Http\Requests\UpdateBlogRequest;
use Domains\CMS\Http\Resources\BlogCollection;
use Domains\CMS\Http\Resources\BlogResource;
use Domains\CMS\Models\Blog;
use Domains\Identity\Models\ApiKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected PublishBlog $publishBlog,
        protected UnpublishBlog $unpublishBlog,
    ) {
        //
    }

    public function list(Request $request): BlogCollection
    {
        abort_unless($request->user()?->can('cms:blogs:view'), 403);

        $blogs = $this->restrictToPublishedForApiKeys(
            Blog::query()->where('company_id', $request->user()->getCompanyId())->with(['author', 'category']),
            $request,
        )->latest()->paginate();

        return new BlogCollection($blogs);
    }

    public function create(CreateBlogRequest $request): JsonResponse
    {
        $blog = Blog::create($request->validated());

        return response()->json(['data' => new BlogResource($blog)], 201);
    }

    public function get(Request $request, int $id): BlogResource
    {
        abort_unless($request->user()?->can('cms:blogs:view'), 403);

        $blog = $this->restrictToPublishedForApiKeys(
            Blog::query()->where('company_id', $request->user()->getCompanyId())->with(['author', 'category']),
            $request,
        )->findOrFail($id);

        return new BlogResource($blog);
    }

    public function update(UpdateBlogRequest $request, int $id): BlogResource
    {
        $blog = Blog::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($id);

        $blog->update($request->validated());

        return new BlogResource($blog);
    }

    public function delete(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('cms:blogs:delete'), 403);

        Blog::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    public function publish(Request $request, int $id): BlogResource
    {
        abort_unless($request->user()?->can('cms:blogs:publish'), 403);

        $blog = Blog::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($id);

        return new BlogResource($this->publishBlog->handle($blog));
    }

    public function unpublish(Request $request, int $id): BlogResource
    {
        abort_unless($request->user()?->can('cms:blogs:publish'), 403);

        $blog = Blog::query()->where('company_id', $request->user()->getCompanyId())->findOrFail($id);

        return new BlogResource($this->unpublishBlog->handle($blog));
    }

    /**
     * ApiKey actors (public-website integrations) can never see draft/
     * unpublished blogs, regardless of their granted abilities — a
     * hard-coded restriction, not a permission string.
     */
    protected function restrictToPublishedForApiKeys(Builder $query, Request $request): Builder
    {
        if ($request->user() instanceof ApiKey || ! $request->user()->can('cms:blogs:create')) {
            $query->published();
        }

        return $query;
    }
}
