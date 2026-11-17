<?php

namespace Domains\CMS\Database\Seeders;

use Domains\CMS\Enums\CMSTrigger;
use Domains\Core\Enums\AppCode;
use Domains\Notification\Database\Seeders\BaseTriggerSeeder;

class CMSTriggerSeeder extends BaseTriggerSeeder
{
    protected function appCode(): AppCode
    {
        return AppCode::CMS;
    }

    protected function data(): array
    {
        return [
            [
                'trigger' => CMSTrigger::BLOG_CREATED,
                'label' => 'Blog Created',
                'description' => 'Fires when a new blog post is created, in any status.',
            ],
            [
                'trigger' => CMSTrigger::BLOG_UPDATED,
                'label' => 'Blog Updated',
                'description' => "Fires when a blog post's content or settings are changed.",
            ],
            [
                'trigger' => CMSTrigger::BLOG_DELETED,
                'label' => 'Blog Deleted',
                'description' => 'Fires when a blog post is deleted.',
            ],
            [
                'trigger' => CMSTrigger::BLOG_STATUS_CHANGED,
                'label' => 'Blog Status Changed',
                'description' => "Fires when a blog post's status changes, e.g. from Draft to Scheduled.",
            ],
            [
                'trigger' => CMSTrigger::BLOG_PUBLISHED,
                'label' => 'Blog Published',
                'description' => 'Fires when a blog post is published and becomes publicly visible.',
            ],
            [
                'trigger' => CMSTrigger::BLOG_UNPUBLISHED,
                'label' => 'Blog Unpublished',
                'description' => 'Fires when a published blog post is taken offline.',
            ],
            [
                'trigger' => CMSTrigger::BLOG_SCHEDULED,
                'label' => 'Blog Scheduled',
                'description' => 'Fires when a blog post is scheduled to be published later.',
            ],
            [
                'trigger' => CMSTrigger::BLOG_ARCHIVED,
                'label' => 'Blog Archived',
                'description' => 'Fires when a blog post is archived.',
            ],
            [
                'trigger' => CMSTrigger::COMMENT_POSTED,
                'label' => 'Comment Posted',
                'description' => 'Fires when a visitor posts a comment on a blog post.',
            ],
            [
                'trigger' => CMSTrigger::COMMENT_APPROVED,
                'label' => 'Comment Approved',
                'description' => 'Fires when a comment is approved and becomes publicly visible.',
            ],
            [
                'trigger' => CMSTrigger::COMMENT_REJECTED,
                'label' => 'Comment Rejected',
                'description' => 'Fires when a comment is rejected during moderation.',
            ],
            [
                'trigger' => CMSTrigger::COMMENT_MARKED_SPAM,
                'label' => 'Comment Marked as Spam',
                'description' => 'Fires when a comment is marked as spam, by a moderator or the spam filter.',
            ],
            [
                'trigger' => CMSTrigger::COMMENT_DELETED,
                'label' => 'Comment Deleted',
                'description' => 'Fires when a comment is deleted.',
            ],
            [
                'trigger' => CMSTrigger::PAGE_CREATED,
                'label' => 'Page Created',
                'description' => 'Fires when a new page is created, in any status.',
            ],
            [
                'trigger' => CMSTrigger::PAGE_UPDATED,
                'label' => 'Page Updated',
                'description' => "Fires when a page's content or settings are changed.",
            ],
            [
                'trigger' => CMSTrigger::PAGE_DELETED,
                'label' => 'Page Deleted',
                'description' => 'Fires when a page is deleted.',
            ],
            [
                'trigger' => CMSTrigger::PAGE_PUBLISHED,
                'label' => 'Page Published',
                'description' => 'Fires when a page is published and becomes publicly visible.',
            ],
            [
                'trigger' => CMSTrigger::PAGE_UNPUBLISHED,
                'label' => 'Page Unpublished',
                'description' => 'Fires when a published page is taken offline.',
            ],
            [
                'trigger' => CMSTrigger::FORM_CREATED,
                'label' => 'Form Created',
                'description' => 'Fires when a new form is created.',
            ],
            [
                'trigger' => CMSTrigger::FORM_UPDATED,
                'label' => 'Form Updated',
                'description' => "Fires when a form's settings, fields or actions are changed.",
            ],
            [
                'trigger' => CMSTrigger::FORM_DELETED,
                'label' => 'Form Deleted',
                'description' => 'Fires when a form is deleted.',
            ],
            [
                'trigger' => CMSTrigger::FORM_ACTIVATED,
                'label' => 'Form Activated',
                'description' => 'Fires when a form is switched on and starts accepting submissions.',
            ],
            [
                'trigger' => CMSTrigger::FORM_DEACTIVATED,
                'label' => 'Form Deactivated',
                'description' => 'Fires when a form is switched off and stops accepting submissions.',
            ],
            [
                'trigger' => CMSTrigger::FORM_SUBMITTED,
                'label' => 'Form Submitted',
                'description' => 'Fires when someone submits one of your forms.',
            ],
            [
                'trigger' => CMSTrigger::FORM_SUBMISSION_STATUS_CHANGED,
                'label' => 'Form Submission Status Changed',
                'description' => "Fires when a form submission's status changes, e.g. from New to Read.",
            ],
            [
                'trigger' => CMSTrigger::FORM_SUBMISSION_MARKED_READ,
                'label' => 'Form Submission Marked as Read',
                'description' => 'Fires when a form submission is marked as read.',
            ],
            [
                'trigger' => CMSTrigger::FORM_SUBMISSION_MARKED_SPAM,
                'label' => 'Form Submission Marked as Spam',
                'description' => 'Fires when a form submission is marked as spam.',
            ],
            [
                'trigger' => CMSTrigger::FORM_SUBMISSION_DELETED,
                'label' => 'Form Submission Deleted',
                'description' => 'Fires when a form submission is deleted.',
            ],
            [
                'trigger' => CMSTrigger::CATEGORY_CREATED,
                'label' => 'Category Created',
                'description' => 'Fires when a new CMS category is created.',
            ],
            [
                'trigger' => CMSTrigger::CATEGORY_UPDATED,
                'label' => 'Category Updated',
                'description' => 'Fires when a CMS category is changed.',
            ],
            [
                'trigger' => CMSTrigger::CATEGORY_DELETED,
                'label' => 'Category Deleted',
                'description' => 'Fires when a CMS category is deleted.',
            ],
            [
                'trigger' => CMSTrigger::TEMPLATE_CREATED,
                'label' => 'Template Created',
                'description' => 'Fires when a new page or blog template is created.',
            ],
            [
                'trigger' => CMSTrigger::TEMPLATE_UPDATED,
                'label' => 'Template Updated',
                'description' => 'Fires when a template is changed.',
            ],
            [
                'trigger' => CMSTrigger::TEMPLATE_DELETED,
                'label' => 'Template Deleted',
                'description' => 'Fires when a template is deleted.',
            ],
            [
                'trigger' => CMSTrigger::MEDIA_UPLOADED,
                'label' => 'Media Uploaded',
                'description' => 'Fires when a file is uploaded to the CMS media library.',
            ],
            [
                'trigger' => CMSTrigger::MEDIA_DELETED,
                'label' => 'Media Deleted',
                'description' => 'Fires when a file is deleted from the CMS media library.',
            ],
            [
                'trigger' => CMSTrigger::SITE_CREATED,
                'label' => 'Site Created',
                'description' => 'Fires when a new CMS site is created.',
            ],
            [
                'trigger' => CMSTrigger::SITE_UPDATED,
                'label' => 'Site Updated',
                'description' => "Fires when a CMS site's details or settings, such as comment moderation, are changed.",
            ],
            [
                'trigger' => CMSTrigger::SITE_DELETED,
                'label' => 'Site Deleted',
                'description' => 'Fires when a CMS site is deleted.',
            ],
        ];
    }
}
