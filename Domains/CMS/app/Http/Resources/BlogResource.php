<?php

namespace Domains\CMS\Http\Resources;

use Domains\CMS\Models\Blog;
use Domains\Core\Http\Resources\BaseResource;
use Domains\Identity\Http\Resources\UserResource;
use Illuminate\Http\Request;

/**
 * @mixin Blog
 */
class BlogResource extends BaseResource
{
    public string $routeName = 'api.cms.blogs';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            /**
             * The identifier of the company the blog post belongs to.
             */
            'company_id' => $this->company_id,

            /**
             * The blog post's title.
             */
            'title' => $this->title,

            /**
             * A URL-friendly identifier for the blog post, unique per company.
             */
            'slug' => $this->slug,

            /**
             * A short summary shown in listings and previews.
             */
            'excerpt' => $this->excerpt,

            /**
             * The blog post's full body content.
             */
            'content' => $this->content,

            /**
             * The URL of an image representing the blog post.
             */
            'featured_image' => $this->featured_image,

            'author_id' => $this->author_id,

            /**
             * The user credited as the blog post's author, if loaded.
             *
             * @var UserResource
             */
            'author' => UserResource::make($this->whenLoaded('author')),

            'category_id' => $this->category_id,

            /**
             * The category this blog post belongs to, if any and if loaded.
             *
             * @var CategoryResource
             */
            'category' => CategoryResource::make($this->whenLoaded('category')),

            /**
             * The blog post's publishing state. See `BlogStatus` for the allowed values.
             */
            'status' => $this->status,

            /**
             * The date and time the blog post was published. `null` while it is still a draft.
             */
            'published_at' => $this->published_at,

            /**
             * The page title used for search engines, if different from `title`.
             */
            'meta_title' => $this->meta_title,

            /**
             * The page description used for search engines.
             */
            'meta_description' => $this->meta_description,

            /**
             * The canonical URL for this blog post's page.
             */
            'canonical_url' => $this->canonical_url,

            /**
             * A freeform array of strings used for search and filtering.
             */
            'tags' => $this->tags,
        ]);
    }
}
