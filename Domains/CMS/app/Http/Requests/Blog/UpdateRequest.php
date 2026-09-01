<?php

namespace Domains\CMS\Http\Requests\Blog;

use Domains\CMS\Enums\BlogStatus;
use Domains\CMS\Enums\CmsPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CmsPermission::BLOG_UPDATE->value);
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();

        return [
            /**
             * The blog post's title.
             *
             * @example How We Cut Onboarding Time in Half
             */
            'title' => ['sometimes', 'string', 'max:255'],

            /**
             * A URL-friendly identifier for the blog post, unique per company.
             *
             * @example how-we-cut-onboarding-time-in-half
             */
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('blogs')->where('company_id', $companyId)->ignore($this->route('id'))],

            /**
             * A short summary shown in listings and previews.
             *
             * @example A look at the workflow changes that halved our new customer onboarding time.
             */
            'excerpt' => ['nullable', 'string'],

            /**
             * The blog post's full body content.
             */
            'content' => ['nullable', 'string'],

            /**
             * The URL of an image representing the blog post.
             *
             * @example https://example.com/images/onboarding-time.jpg
             */
            'featured_image' => ['nullable', 'string', 'max:255'],

            /**
             * The user credited as the blog post's author.
             *
             * @example 7
             */
            'author_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],

            /**
             * The category this blog post belongs to.
             *
             * @example 3
             */
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('company_id', $companyId)],

            /**
             * The blog post's publishing state. See `BlogStatus` for the allowed values.
             *
             * @example Published
             */
            'status' => ['nullable', Rule::enum(BlogStatus::class)],

            /**
             * The page title used for search engines, if different from `title`.
             *
             * @example How We Cut Onboarding Time in Half | Acme Blog
             */
            'meta_title' => ['nullable', 'string', 'max:255'],

            /**
             * The page description used for search engines.
             *
             * @example Learn the workflow changes that halved our new customer onboarding time.
             */
            'meta_description' => ['nullable', 'string', 'max:500'],

            /**
             * The canonical URL for this blog post's page, used to avoid duplicate-content
             * issues with search engines.
             *
             * @example https://example.com/blog/how-we-cut-onboarding-time-in-half
             */
            'canonical_url' => ['nullable', 'string', 'max:255'],

            /**
             * A freeform array of strings used for search and filtering.
             *
             * @example ["onboarding", "product"]
             */
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ];
    }
}
