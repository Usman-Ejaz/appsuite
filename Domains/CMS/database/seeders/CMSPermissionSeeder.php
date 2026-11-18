<?php

namespace Domains\CMS\Database\Seeders;

use Domains\CMS\Enums\CMSPermission;
use Domains\Core\Enums\AppCode;
use Domains\Identity\Database\Seeders\BasePermissionSeeder;

class CMSPermissionSeeder extends BasePermissionSeeder
{
    protected function appCode(): AppCode
    {
        return AppCode::CMS;
    }

    protected function data(): array
    {
        return [
            [
                'permission' => CMSPermission::BLOG_VIEW,
                'label' => 'View Blogs',
                'description' => 'View blog posts, including drafts and scheduled posts.',
            ],
            [
                'permission' => CMSPermission::BLOG_CREATE,
                'label' => 'Create Blogs',
                'description' => 'Write new blog posts.',
            ],
            [
                'permission' => CMSPermission::BLOG_UPDATE,
                'label' => 'Update Blogs',
                'description' => 'Edit existing blog posts.',
            ],
            [
                'permission' => CMSPermission::BLOG_DELETE,
                'label' => 'Delete Blogs',
                'description' => 'Remove blog posts.',
            ],
            [
                'permission' => CMSPermission::BLOG_PUBLISH,
                'label' => 'Publish Blogs',
                'description' => 'Publish, schedule, or unpublish blog posts.',
            ],
            [
                'permission' => CMSPermission::FORM_VIEW,
                'label' => 'View Forms',
                'description' => 'View forms and their fields.',
            ],
            [
                'permission' => CMSPermission::FORM_CREATE,
                'label' => 'Create Forms',
                'description' => 'Create new forms.',
            ],
            [
                'permission' => CMSPermission::FORM_UPDATE,
                'label' => 'Update Forms',
                'description' => 'Edit forms, including their fields and actions.',
            ],
            [
                'permission' => CMSPermission::FORM_DELETE,
                'label' => 'Delete Forms',
                'description' => 'Remove forms.',
            ],
            [
                'permission' => CMSPermission::FORM_SUBMIT,
                'label' => 'Submit Forms',
                'description' => 'Submit entries to forms.',
            ],
            [
                'permission' => CMSPermission::FORM_SUBMISSIONS_VIEW,
                'label' => 'View Form Submissions',
                'description' => 'View the entries submitted to forms.',
            ],
            [
                'permission' => CMSPermission::FORM_SUBMISSIONS_UPDATE,
                'label' => 'Update Form Submissions',
                'description' => 'Change the status of form submissions, e.g. mark them as read or spam.',
            ],
            [
                'permission' => CMSPermission::FORM_SUBMISSIONS_DELETE,
                'label' => 'Delete Form Submissions',
                'description' => 'Remove form submissions.',
            ],
            [
                'permission' => CMSPermission::CATEGORIES_VIEW,
                'label' => 'View Categories',
                'description' => 'View CMS categories.',
            ],
            [
                'permission' => CMSPermission::CATEGORIES_MANAGE,
                'label' => 'Manage Categories',
                'description' => 'Create, edit, and delete CMS categories.',
            ],
            [
                'permission' => CMSPermission::PAGE_VIEW,
                'label' => 'View Pages',
                'description' => 'View pages, including drafts.',
            ],
            [
                'permission' => CMSPermission::PAGE_CREATE,
                'label' => 'Create Pages',
                'description' => 'Create new pages.',
            ],
            [
                'permission' => CMSPermission::PAGE_UPDATE,
                'label' => 'Update Pages',
                'description' => 'Edit existing pages.',
            ],
            [
                'permission' => CMSPermission::PAGE_DELETE,
                'label' => 'Delete Pages',
                'description' => 'Remove pages.',
            ],
            [
                'permission' => CMSPermission::PAGE_PUBLISH,
                'label' => 'Publish Pages',
                'description' => 'Publish or unpublish pages.',
            ],
            [
                'permission' => CMSPermission::TEMPLATE_VIEW,
                'label' => 'View Page Templates',
                'description' => 'View page and blog templates.',
            ],
            [
                'permission' => CMSPermission::TEMPLATE_CREATE,
                'label' => 'Create Page Templates',
                'description' => 'Create new templates.',
            ],
            [
                'permission' => CMSPermission::TEMPLATE_UPDATE,
                'label' => 'Update Page Templates',
                'description' => 'Edit existing templates.',
            ],
            [
                'permission' => CMSPermission::TEMPLATE_DELETE,
                'label' => 'Delete Page Templates',
                'description' => 'Remove templates.',
            ],
            [
                'permission' => CMSPermission::MEDIA_VIEW,
                'label' => 'View Media Library',
                'description' => 'View files in the media library.',
            ],
            [
                'permission' => CMSPermission::MEDIA_MANAGE,
                'label' => 'Manage Media Library',
                'description' => 'Upload and delete files in the media library.',
            ],
            [
                'permission' => CMSPermission::COMMENT_VIEW,
                'label' => 'View Comments',
                'description' => 'View comments left on blog posts.',
            ],
            [
                'permission' => CMSPermission::COMMENT_MODERATE,
                'label' => 'Moderate Comments',
                'description' => 'Approve, reject, or mark comments as spam.',
            ],
            [
                'permission' => CMSPermission::COMMENT_DELETE,
                'label' => 'Delete Comments',
                'description' => 'Remove comments.',
            ],
        ];
    }
}
