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
            'company_id' => $this->company_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image,
            'author_id' => $this->author_id,
            'author' => UserResource::make($this->whenLoaded('author')),
            'category_id' => $this->category_id,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'status' => $this->status,
            'published_at' => $this->published_at,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'canonical_url' => $this->canonical_url,
            'tags' => $this->tags,
        ]);
    }
}
