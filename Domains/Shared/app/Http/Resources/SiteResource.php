<?php

namespace Domains\Shared\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Shared\Models\Site;
use Illuminate\Http\Request;

/**
 * @mixin Site
 */
class SiteResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $this->routeName = 'api.sites';

        return [
            'id' => $this->id,

            /**
             * The identifier of the company the site belongs to.
             */
            'company_id' => $this->whenHas('company_id'),

            /**
             * The site's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * The site's page title used for search engines and browser tabs, if different
             * from `name`.
             */
            'title' => $this->whenHas('title'),

            /**
             * A short tag line shown alongside the site's name.
             */
            'tag_line' => $this->whenHas('tag_line'),

            /**
             * The site's base URL.
             */
            'url' => $this->whenHas('url'),

            /**
             * The site's stage in its publishing lifecycle.
             */
            'status' => $this->whenHas('status'),

            /**
             * A freeform bag of site-level settings.
             */
            'setting' => $this->whenHas('setting'),

            /**
             * Whether visitors can leave comments on blog posts.
             */
            'allow_comments_on_blogs' => $this->whenHas('allow_comments_on_blogs'),

            /**
             * Whether a new comment must be approved before it becomes visible.
             */
            'requires_comment_approval' => $this->whenHas('requires_comment_approval'),

            /**
             * Whether the company is notified when a new comment is left.
             */
            'notify_on_comments' => $this->whenHas('notify_on_comments'),

            /**
             * Whether incoming comments are screened for spam.
             */
            'spam_filtering' => $this->whenHas('spam_filtering'),

            /**
             * A newline or comma separated list of keywords that mark a comment as spam.
             */
            'blocklist_keywords' => $this->whenHas('blocklist_keywords'),

            $this->merge(parent::toArray($request)),
        ];
    }
}
