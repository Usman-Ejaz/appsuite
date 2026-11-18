<?php

namespace Domains\CMS\Enums;

/**
 * The CMS permissions a role can be granted. Each value is the permission's `name`, in the
 * `cms:<resource>:<action>` shape the authorization gate checks; its label and description live in
 * `CMSPermissionSeeder`.
 */
enum CMSPermission: string
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
}
