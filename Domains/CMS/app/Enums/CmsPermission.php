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
            default => null
        };
    }
}
