<?php

namespace Domains\CMS\Enums;

enum CmsPermission: string
{
    case BLOG_VIEW = 'cms:blogs:view';
    case BLOG_CREATE = 'cms:blogs:create';
    case BLOG_UPDATE = 'cms:blogs:update';
    case BLOG_DELETE = 'cms:blogs:delete';
    case BLOG_PUBLISH = 'cms:blogs:publish';

    case FORM_VIEW = 'cms:forms:view';
    case FORM_CREATE = 'cms:forms:create';
    case FORM_UPDATE = 'cms:forms:update';
    case FORM_DELETE = 'cms:forms:delete';
    case FORM_SUBMIT = 'cms:forms:submit';

    case FORM_SUBMISSIONS_VIEW = 'cms:submissions:view';
    case FORM_SUBMISSIONS_UPDATE = 'cms:submissions:update';
    case FORM_SUBMISSIONS_DELETE = 'cms:submissions:delete';

    case CATEGORIES_VIEW = 'cms:categories:view';
    case CATEGORIES_MANAGE = 'cms:categories:manage';

    case PAGE_VIEW = 'cms:pages:view';
    case PAGE_CREATE = 'cms:pages:create';
    case PAGE_UPDATE = 'cms:pages:update';
    case PAGE_DELETE = 'cms:pages:delete';
    case PAGE_PUBLISH = 'cms:pages:publish';

    case TEMPLATE_VIEW = 'cms:templates:view';
    case TEMPLATE_CREATE = 'cms:templates:create';
    case TEMPLATE_UPDATE = 'cms:templates:update';
    case TEMPLATE_DELETE = 'cms:templates:delete';

    case MEDIA_VIEW = 'cms:media:view';
    case MEDIA_MANAGE = 'cms:media:manage';

    case COMMENT_VIEW = 'cms:comments:view';
    case COMMENT_MODERATE = 'cms:comments:moderate';
    case COMMENT_DELETE = 'cms:comments:delete';

    public function label(): ?string
    {
        return match ($this) {
            self::BLOG_VIEW => 'View Blogs',
            self::BLOG_CREATE => 'Create Blogs',
            self::BLOG_UPDATE => 'Update Blogs',
            self::BLOG_DELETE => 'Delete Blogs',
            self::BLOG_PUBLISH => 'Publish Blogs',
            self::FORM_VIEW => 'View Forms',
            self::FORM_CREATE => 'Create Forms',
            self::FORM_UPDATE => 'Update Forms',
            self::FORM_DELETE => 'Delete Forms',
            self::FORM_SUBMIT => 'Submit Forms',
            self::FORM_SUBMISSIONS_VIEW => 'View Form Submissions',
            self::FORM_SUBMISSIONS_UPDATE => 'Update Form Submissions',
            self::FORM_SUBMISSIONS_DELETE => 'Delete Form Submissions',
            self::CATEGORIES_VIEW => 'View Categories',
            self::CATEGORIES_MANAGE => 'Manage Categories',
            self::PAGE_VIEW => 'View Pages',
            self::PAGE_CREATE => 'Create Pages',
            self::PAGE_UPDATE => 'Update Pages',
            self::PAGE_DELETE => 'Delete Pages',
            self::PAGE_PUBLISH => 'Publish Pages',
            self::TEMPLATE_VIEW => 'View Page Templates',
            self::TEMPLATE_CREATE => 'Create Page Templates',
            self::TEMPLATE_UPDATE => 'Update Page Templates',
            self::TEMPLATE_DELETE => 'Delete Page Templates',
            self::MEDIA_VIEW => 'View Media Library',
            self::MEDIA_MANAGE => 'Manage Media Library',
            self::COMMENT_VIEW => 'View Comments',
            self::COMMENT_MODERATE => 'Moderate Comments',
            self::COMMENT_DELETE => 'Delete Comments',
            default => null
        };
    }

    public function code(): ?string
    {
        return match ($this) {
            self::BLOG_VIEW => 'cms_blogs_view',
            self::BLOG_CREATE => 'cms_blogs_create',
            self::BLOG_UPDATE => 'cms_blogs_update',
            self::BLOG_DELETE => 'cms_blogs_delete',
            self::BLOG_PUBLISH => 'cms_blogs_publish',
            self::FORM_VIEW => 'cms_forms_view',
            self::FORM_CREATE => 'cms_forms_create',
            self::FORM_UPDATE => 'cms_forms_update',
            self::FORM_DELETE => 'cms_forms_delete',
            self::FORM_SUBMIT => 'cms_forms_submit',
            self::FORM_SUBMISSIONS_VIEW => 'cms_submissions_view',
            self::FORM_SUBMISSIONS_UPDATE => 'cms_submissions_update',
            self::FORM_SUBMISSIONS_DELETE => 'cms_submissions_delete',
            self::CATEGORIES_VIEW => 'cms_categories_view',
            self::CATEGORIES_MANAGE => 'cms_categories_manage',
            self::PAGE_VIEW => 'cms_pages_view',
            self::PAGE_CREATE => 'cms_pages_create',
            self::PAGE_UPDATE => 'cms_pages_update',
            self::PAGE_DELETE => 'cms_pages_delete',
            self::PAGE_PUBLISH => 'cms_pages_publish',
            self::TEMPLATE_VIEW => 'cms_templates_view',
            self::TEMPLATE_CREATE => 'cms_templates_create',
            self::TEMPLATE_UPDATE => 'cms_templates_update',
            self::TEMPLATE_DELETE => 'cms_templates_delete',
            self::MEDIA_VIEW => 'cms_media_view',
            self::MEDIA_MANAGE => 'cms_media_manage',
            self::COMMENT_VIEW => 'cms_comments_view',
            self::COMMENT_MODERATE => 'cms_comments_moderate',
            self::COMMENT_DELETE => 'cms_comments_delete',
            default => null
        };
    }

    public function description(): ?string
    {
        return match ($this) {
            self::BLOG_VIEW => 'blogs_view',
            self::BLOG_CREATE => 'blogs_create',
            self::BLOG_UPDATE => 'blogs_update',
            self::BLOG_DELETE => 'blogs_delete',
            self::BLOG_PUBLISH => 'blogs_publish',
            self::FORM_VIEW => 'forms_view',
            self::FORM_CREATE => 'forms_create',
            self::FORM_UPDATE => 'forms_update',
            self::FORM_DELETE => 'forms_delete',
            self::FORM_SUBMIT => 'forms_submit',
            self::FORM_SUBMISSIONS_VIEW => 'submissions_view',
            self::FORM_SUBMISSIONS_UPDATE => 'submissions_update',
            self::FORM_SUBMISSIONS_DELETE => 'submissions_delete',
            self::CATEGORIES_VIEW => 'categories_view',
            self::CATEGORIES_MANAGE => 'categories_manage',
            self::PAGE_VIEW => 'pages_view',
            self::PAGE_CREATE => 'pages_create',
            self::PAGE_UPDATE => 'pages_update',
            self::PAGE_DELETE => 'pages_delete',
            self::PAGE_PUBLISH => 'pages_publish',
            self::TEMPLATE_VIEW => 'templates_view',
            self::TEMPLATE_CREATE => 'templates_create',
            self::TEMPLATE_UPDATE => 'templates_update',
            self::TEMPLATE_DELETE => 'templates_delete',
            self::MEDIA_VIEW => 'media_view',
            self::MEDIA_MANAGE => 'media_manage',
            self::COMMENT_VIEW => 'comments_view',
            self::COMMENT_MODERATE => 'comments_moderate',
            self::COMMENT_DELETE => 'comments_delete',
            default => null
        };
    }
}
