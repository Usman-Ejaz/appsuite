<?php

namespace Domains\Shared\Http\Requests\Site;

use Domains\Shared\Enums\SiteStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * The site's display name.
             *
             * @example Acme Blog
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * The site's page title used for search engines and browser tabs, if different
             * from `name`.
             *
             * @example Acme Blog | Latest News
             */
            'title' => ['nullable', 'string', 'max:255'],

            /**
             * A short tag line shown alongside the site's name.
             *
             * @example News, tips, and updates from Acme
             */
            'tag_line' => ['nullable', 'string', 'max:255'],

            /**
             * The site's base URL.
             *
             * @example https://blog.acme.com
             */
            'url' => ['sometimes', 'string', 'max:255', 'url'],

            /**
             * The site's stage in its publishing lifecycle.
             *
             * @example Active
             */
            'status' => ['nullable', Rule::enum(SiteStatus::class)],

            /**
             * A freeform bag of site-level settings.
             *
             * @example {"theme": "dark"}
             */
            'setting' => ['nullable', 'array'],

            /**
             * Whether visitors can leave comments on blog posts.
             *
             * @example true
             */
            'allow_comments_on_blogs' => ['nullable', 'boolean'],

            /**
             * Whether a new comment must be approved before it becomes visible.
             *
             * @example true
             */
            'requires_comment_approval' => ['nullable', 'boolean'],

            /**
             * Whether the company is notified when a new comment is left.
             *
             * @example true
             */
            'notify_on_comments' => ['nullable', 'boolean'],

            /**
             * Whether incoming comments are screened for spam.
             *
             * @example true
             */
            'spam_filtering' => ['nullable', 'boolean'],

            /**
             * A newline or comma separated list of keywords that mark a comment as spam.
             *
             * @example viagra, casino
             */
            'blocklist_keywords' => ['nullable', 'string'],
        ];
    }
}
