<?php

namespace Domains\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\CMS\Actions\BlogPublisher;
use Domains\CMS\Enums\CmsPermission;
use Domains\CMS\Http\Requests\Blog\CreateRequest;
use Domains\CMS\Http\Requests\Blog\UpdateRequest;
use Domains\CMS\Http\Resources\BlogResource;
use Domains\CMS\Repositories\BlogRepository;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Illuminate\Http\JsonResponse;

#[Group('CMS')]
class BlogController extends Controller
{
    public function __construct(
        protected BlogRepository $blogs
    ) {
        //
    }

    /**
     * Get Blogs
     *
     * Returns the blog posts that belong to the company.
     */
    public function list(GetCollectionRequest $request)
    {
        $this->authorize('permission', CmsPermission::BLOG_VIEW);

        $filters = $request->filters();

        $records = $this->blogs->list($filters);

        return BlogResource::collection($records);
    }

    /**
     * Create Blog
     *
     * Creates a new blog post for the company. The `slug` must be unique among the company's
     * other blog posts. A newly created blog post starts as `Draft` unless `status` is set
     * explicitly.
     */
    public function create(CreateRequest $request)
    {
        $input = $request->validated();

        $record = $this->blogs->create($input);

        return BlogResource::make($record);
    }

    /**
     * Get Blog
     *
     * Returns the details of a single blog post.
     */
    public function get(GetResourceRequest $request, int $id): BlogResource
    {
        $this->authorize('permission', CmsPermission::BLOG_VIEW);

        $filters = $request->filters();

        $record = $this->blogs->get($id, $filters);

        return BlogResource::make($record);
    }

    /**
     * Update Blog
     *
     * Updates an existing blog post. Fields left out of the request keep their current value.
     */
    public function update(UpdateRequest $request, int $id): BlogResource
    {
        $input = $request->validated();

        $blog = $this->blogs->update($id, $input);

        return BlogResource::make($blog);
    }

    /**
     * Delete Blog
     *
     * Permanently removes a blog post.
     */
    public function delete(int $id): JsonResponse
    {
        $this->authorize('permission', CmsPermission::BLOG_DELETE);

        $this->blogs->delete($id);

        return response()->json(null, 204);
    }

    /**
     * Publish Blog
     *
     * Makes a blog post publicly visible, setting its status to `Published` and stamping
     * `published_at` with the current time.
     */
    public function publish(int $id): BlogResource
    {
        $this->authorize('permission', CmsPermission::BLOG_PUBLISH);

        $blog = BlogPublisher::publish(
            $this->blogs->findOrFail($id)
        );

        return BlogResource::make($blog);
    }

    /**
     * Unpublish Blog
     *
     * Removes a blog post from public visibility, setting its status back to `Draft`.
     */
    public function unpublish(int $id): BlogResource
    {
        $this->authorize('permission', CmsPermission::BLOG_PUBLISH);

        $blog = BlogPublisher::unpublish(
            $this->blogs->findOrFail($id)
        );

        return BlogResource::make($blog);
    }
}
